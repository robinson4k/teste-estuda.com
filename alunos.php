<?php
require_once 'utils/conn.php';
require_once 'utils/functions.php';

// POST - CADASTRO E ATUALIZAÇÃO
if (isset($_POST['tipo'])) {
// echo "<pre>";
// var_dump($_POST);
// exit;
    if ($_POST['tipo'] == 'update') {
        $stm = $connect->prepare("UPDATE alunos SET nome = :nome, email = :email, telefone = :telefone, data_nascimento = :data_nascimento, genero = :genero WHERE id = :id");
        $stm->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
    } else
        $stm = $connect->prepare("INSERT INTO alunos (nome, email, telefone, data_nascimento, genero) VALUES (:nome, :email, :telefone, :data_nascimento, :genero)");
    $stm->bindValue(':nome', $_POST['nome'], PDO::PARAM_STR);
    $stm->bindValue(':email', $_POST['email'], PDO::PARAM_STR);
    $stm->bindValue(':telefone', $_POST['telefone'], PDO::PARAM_STR);
    $stm->bindValue(':data_nascimento', $_POST['data_nascimento'], PDO::PARAM_STR);
    $stm->bindValue(':genero', $_POST['genero'], PDO::PARAM_STR);
    $done = $stm->execute();
    if ($done) {
        if ($_POST['tipo'] == 'new')
            $id = $connect->lastInsertId();
        else {
            $id = $_POST['id'];

            $stm = $connect->prepare("DELETE FROM aluno_turma WHERE aluno_id = :aluno_id");
            $stm->bindValue(':aluno_id', $id, PDO::PARAM_INT);
            $stm->execute();
        }
        foreach ($_POST['aluno_turma'] as $turma_id) {
            $stm = $connect->prepare("INSERT INTO aluno_turma(aluno_id, turma_id) VALUES (:aluno_id, :turma_id)");
            $stm->bindValue(':aluno_id', $id, PDO::PARAM_INT);
            $stm->bindValue(':turma_id', $turma_id, PDO::PARAM_INT);
            $stm->execute();
        }
    }
    $return = $done ? 'success=true&msg=Dados foram salvos.' : 'success=false&msg=Não foi possível executar esta rotina, tente novamente.';

    header("location: alunos.php?$return");
}

// GET - ENCONTRAR REGISTRO
if (isset($_GET['update']) && is_numeric($_GET['update'])) {
    $stm = $connect->prepare("SELECT * FROM alunos WHERE id = :id");
    $stm->bindValue(':id', $_GET['update'], PDO::PARAM_INT);
    if ($stm->execute()) {
        $set = $stm->fetch(PDO::FETCH_OBJ);

        $stm = $connect->prepare("SELECT turma_id FROM aluno_turma WHERE aluno_id = :aluno_id");
        $stm->bindValue(':aluno_id', $_GET['update'], PDO::PARAM_INT);
        $stm->execute();
        $aluno_turma = $stm->fetchAll(PDO::FETCH_COLUMN);
// echo "<pre>";
// var_dump(in_array(2, $aluno_turma));
// exit;
    } else
        header("location: alunos.php");
}

// GET - DELETAR REGISTRO
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stm = $connect->prepare("DELETE FROM alunos WHERE id = :id");
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
    <h1 class="h3"><?php echo isset($_GET['update']) ? 'EDITAR REGISTRO DA' : 'CADASTRO DE' ?> ALUNO</h1>
    <form method="POST" action="alunos.php">
        <input type="hidden" name="tipo" value="<?php echo isset($_GET['update']) ? 'update' : 'new' ?>">
        <input type="hidden" name="id" value="<?php echo isset($_GET['update']) ? $_GET['update'] : '' ?>">
        <div class="form-row">
            <div class="form-group col">
                <label for="turma">TURMA</label>
                <?php
                $stm = $connect->prepare("SELECT * FROM turmas");
                $stm->execute();
                $turmas = $stm->fetchAll(PDO::FETCH_OBJ);
                ?>
                <select class="form-control selectpicker" name="aluno_turma[]" id="aluno_turma" multiple required>
                    <?php foreach ($turmas as $turma) { ?>
                        <option value="<?php echo $turma->id ?>" <?php echo isset($set) && in_array($turma->id, $aluno_turma) ? 'selected' : '' ?>><?php echo $turma->ano ?>, <?php echo $turma->serie ?>, <?php echo $turma->turno ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-md">
                <label for="nome">NOME</label>
                <input type="text" class="form-control" name="nome" id="nome" value="<?php echo isset($set) ? $set->nome : '' ?>" maxlength="255" required>
            </div>
            <div class="form-group col-md">
                <label for="email">E-MAIL</label>
                <input type="text" class="form-control" name="email" id="email" value="<?php echo isset($set) ? $set->email : '' ?>" maxlength="150" required>
            </div>
            <div class="form-group col-md">
                <label for="telefone">TELEFONE</label>
                <input type="text" class="form-control" name="telefone" id="telefone" value="<?php echo isset($set) ? $set->telefone : '' ?>" maxlength="15">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-auto">
                <label for="data_nascimento">DATA DE NASCIMENTO</label>
                <input type="date" class="form-control datepicker" name="data_nascimento" id="data_nascimento" value="<?php echo isset($set) ? $set->data_nascimento : formataData(date('d/m/Y'), 'd/m/Y') ?>" required>
            </div>
            <div class="form-group col-md">
                <label for="turno">GÊNERO</label>
                <br>
                <label><input type="radio" name="genero" value="F" <?php echo isset($set) && $set->genero == 'F' ? 'checked' : '' ?>> FEMININO</label>
                <label><input type="radio" name="genero" value="M" <?php echo isset($set) && $set->genero == 'M' ? 'checked' : '' ?>> MASCULINO</label>
            </div>
        </div>
        <button type="submit" class="btn btn-success">SALVAR</button>
        <a href="alunos.php" class="btn btn-dark btn-xs">CANCELAR</a>
    </form>
<?php } else {
    $stm = $connect->prepare("SELECT alunos.* FROM alunos
    ORDER BY id DESC");
    $stm->execute();
    $sets = $stm->fetchAll(PDO::FETCH_OBJ);
    ?>
    <h1 class="h3">
        ALUNOS <small>TOTAL <?php echo count($sets) ?></small>
        <a href="?new" class="btn btn-primary btn-xs float-right">NOVO</a>
    </h1>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-dark">
            <thead>
                <tr>
                    <th class="text-right">ID</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Telefone</th>
                    <th>Data nascimento</th>
                    <th>Gênero</th>
                    <th style="width: 1%;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sets as $set) { ?>
                    <tr>
                        <th class="text-right"><?php echo $set->id ?></th>
                        <td><?php echo $set->nome ?></td>
                        <td><?php echo $set->email ?></td>
                        <td><?php echo $set->telefone ?></td>
                        <td><?php echo formataData($set->data_nascimento, 'd/m/Y') ?></td>
                        <td>
                            <?php echo $set->genero == 'F' ? 'FUNDAMENTAL' : 'MÉDIO' ?>
                        </td>
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
