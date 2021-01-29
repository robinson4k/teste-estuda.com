<?php
require_once 'utils/conn.php';
require_once 'utils/functions.php';

// POST - CADASTRO E ATUALIZAÇÃO
if (isset($_POST['tipo'])) {
    if ($_POST['tipo'] == 'update') {
        $stm = $connect->prepare("UPDATE turmas SET escola_id = :escola_id, ano = :ano, nivel_ensino = :nivel_ensino, serie = :serie, turno = :turno WHERE id = :id");
        $stm->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
    } else
        $stm = $connect->prepare("INSERT INTO turmas (escola_id, ano, nivel_ensino, serie, turno) VALUES (:escola_id, :ano, :nivel_ensino, :serie, :turno)");
    $stm->bindValue(':escola_id', $_POST['escola_id'], PDO::PARAM_INT);
    $stm->bindValue(':ano', $_POST['ano'], PDO::PARAM_INT);
    $stm->bindValue(':nivel_ensino', $_POST['nivel_ensino'], PDO::PARAM_STR);
    $stm->bindValue(':serie', $_POST['serie'], PDO::PARAM_STR);
    $stm->bindValue(':turno', $_POST['turno'], PDO::PARAM_STR);
    $done = $stm->execute();
    $return = $done ? 'success=true&msg=Dados foram salvos.' : 'success=false&msg=Não foi possível executar esta rotina, tente novamente.';

    header("location: turmas.php?$return");
}

// GET - ENCONTRAR REGISTRO
if (isset($_GET['update']) && is_numeric($_GET['update'])) {
    $stm = $connect->prepare("SELECT * FROM turmas WHERE id = :id");
    $stm->bindValue(':id', $_GET['update'], PDO::PARAM_INT);
    if ($stm->execute())
        $set = $stm->fetch(PDO::FETCH_OBJ);
    else
        header("location: turmas.php");
}

// GET - DELETAR REGISTRO
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stm = $connect->prepare("DELETE FROM turmas WHERE id = :id");
    $stm->bindValue(':id', $_GET['delete'], PDO::PARAM_INT);
    if ($stm->execute())
        exit(json_encode([
            'success' => true
        ]));
    else
        exit(json_encode([
            'success' => false,
            'error' => 'Não foi possível deletar, tente novamente',
        ]));
}
require_once 'utils/header.php';
require_once 'utils/menu.php';

if (isset($_GET['new']) || isset($_GET['update'])) { ?>
    <h1 class="h3"><?php echo isset($_GET['update']) ? 'EDITAR REGISTRO DA' : 'CADASTRO DE' ?> TURMA</h1>
    <form method="POST" action="turmas.php">
        <input type="hidden" name="tipo" value="<?php echo isset($_GET['update']) ? 'update' : 'new' ?>">
        <input type="hidden" name="id" value="<?php echo isset($_GET['update']) ? $_GET['update'] : '' ?>">
        <div class="form-row">
            <div class="form-group col">
                <label for="nome">ESCOLA</label>
                <?php
                $stm = $connect->prepare("SELECT * FROM escolas ORDER BY nome");
                $stm->execute();
                $escolas = $stm->fetchAll(PDO::FETCH_OBJ);
                ?>
                <select class="form-control" name="escola_id" id="escola_id" required>
                    <option value="">SELECIONE</option>
                    <?php foreach ($escolas as $escola) { ?>
                        <option value="<?php echo $escola->id ?>" <?php echo isset($set) && $set->escola_id == $escola->id ? 'selected' : '' ?>><?php echo $escola->nome ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-md-1">
                <label for="data">ANO</label>
                <input type="text" class="form-control" name="ano" id="ano" value="<?php echo isset($set) ? $set->ano : '' ?>" required>
            </div>

            <div class="form-group col-md-2">
                <label for="nivel_ensino">NÍVEL DE ENSINO</label>
                <select class="form-control" name="nivel_ensino" id="nivel_ensino" required>
                    <option value="">SELECIONE</option>
                    <option value="F" <?php echo isset($set) && $set->nivel_ensino == 'F' ? 'selected' : '' ?>>FUNDAMENTAL</option>
                    <option value="M" <?php echo isset($set) && $set->nivel_ensino == 'M' ? 'selected' : '' ?>>MÉDIO</option>
                </select>
            </div>
            <div class="form-group col-md">
                <label for="serie">SÉRIE</label>
                <input type="text" class="form-control" name="serie" id="serie" value="<?php echo isset($set) ? $set->serie : '' ?>" maxlength="100">
            </div>
            <div class="form-group col-md">
                <label for="turno">TURNO</label>
                <select class="form-control" name="turno" id="turno">
                    <option value="">SELECIONE</option>
                    <option value="MATUTINO" <?php echo isset($set) && $set->turno == 'MATUTINO' ? 'selected' : '' ?>>MATUTINO</option>
                    <option value="VESPERTINO" <?php echo isset($set) && $set->turno == 'VESPERTINO' ? 'selected' : '' ?>>VESPERTINO</option>
                    <option value="NOTURNO" <?php echo isset($set) && $set->turno == 'NOTURNO' ? 'selected' : '' ?>>NOTURNO</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-success">SALVAR</button>
        <a href="turmas.php" class="btn btn-dark btn-xs">CANCELAR</a>
    </form>
<?php } else {
    $stm = $connect->prepare("SELECT turmas.*, escolas.nome FROM turmas
    JOIN escolas ON turmas.escola_id = escolas.id
    ORDER BY id DESC");
    $stm->execute();
    $sets = $stm->fetchAll(PDO::FETCH_OBJ);
    ?>
    <h1 class="h3">
        TURMAS <small>TOTAL <?php echo count($sets) ?></small>
        <a href="?new" class="btn btn-primary btn-xs float-right">NOVO</a>
    </h1>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-dark">
            <thead>
                <tr>
                    <th class="text-right">ID</th>
                    <th>Escola</th>
                    <th>Ano</th>
                    <th>Nível ensino</th>
                    <th>Série</th>
                    <th>Turno</th>
                    <th style="width: 1%;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sets as $set) { ?>
                    <tr>
                        <th class="text-right"><?php echo $set->id ?></th>
                        <td><?php echo $set->nome ?></td>
                        <td><?php echo $set->ano ?></td>
                        <td>
                            <?php echo $set->nivel_ensino == 'F' ? 'FUNDAMENTAL' : 'MÉDIO' ?>
                        </td>
                        <td><?php echo $set->serie ?></td>
                        <td><?php echo $set->turno ?></td>
                        <td>
                            <div class="btn-group btn-sm">
                                <a href="?update=<?php echo $set->id; ?>" class="btn btn-primary btn-xs">Editar</a>
                                <button type="button" onclick="excluir('?delete=<?php echo $set->id; ?>')" class="btn btn-danger btn-xs">Deletar</button>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
<?php }
require_once 'utils/footer.php';
