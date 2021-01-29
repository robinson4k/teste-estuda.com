<?php
require_once 'utils/conn.php';
require_once 'utils/functions.php';

// POST - CADASTRO E ATUALIZAÇÃO
if (isset($_POST['tipo'])) {
    if ($_POST['tipo'] == 'update') {
        $stm = $connect->prepare("UPDATE escolas SET nome = :nome, data = :data, cep = :cep, endereco = :endereco, situacao = :situacao WHERE id = :id");
        $stm->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
    } else
        $stm = $connect->prepare("INSERT INTO escolas (nome, data, cep, endereco, situacao) VALUES (:nome, :data, :cep, :endereco, :situacao)");
    $stm->bindValue(':nome', $_POST['nome'], PDO::PARAM_STR);
    $stm->bindValue(':data', formataData($_POST['data'], 'Y-m-d'), PDO::PARAM_STR);
    $stm->bindValue(':cep', limpar(trim($_POST['cep'])), PDO::PARAM_STR);
    $stm->bindValue(':endereco', $_POST['endereco'], PDO::PARAM_STR);
    $stm->bindValue(':situacao', $_POST['situacao'], PDO::PARAM_STR);
    $done = $stm->execute();
    $return = $done ? 'success=true&msg=Dados foram salvos.' : 'success=false&msg=Não foi possível executar esta rotina, tente novamente.';

    header("location: escolas.php?$return");
}


// GET - ENCONTRAR REGISTRO
if (isset($_GET['update']) && is_numeric($_GET['update'])) {
    $stm = $connect->prepare("SELECT * FROM escolas WHERE id = :id");
    $stm->bindValue(':id', $_GET['update'], PDO::PARAM_INT);
    if ($stm->execute())
        $set = $stm->fetch(PDO::FETCH_OBJ);
    else
        header("location: escolas.php");
}


// GET - DELETAR REGISTRO
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stm = $connect->prepare("DELETE FROM escolas WHERE id = :id");
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
    <h1 class="h3"><?php echo isset($_GET['update']) ? 'EDITAR REGISTRO DA' : 'CADASTRO DE' ?> ESCOLA</h1>
    <form method="POST" action="escolas.php">
        <input type="hidden" name="tipo" value="<?php echo isset($_GET['update']) ? 'update' : 'new' ?>">
        <input type="hidden" name="id" value="<?php echo isset($_GET['update']) ? $_GET['update'] : '' ?>">
        <div class="form-row">
            <div class="form-group col">
                <label for="nome">NOME</label>
                <input type="text" class="form-control" name="nome" id="nome" value="<?php echo isset($set) ? $set->nome : '' ?>" maxlength="255" required>
            </div>
            <div class="form-group col-md-auto">
                <label for="data">DATA</label>
                <input type="date" class="form-control" name="data" id="data" value="<?php echo isset($set) ? $set->data : formataData(date('d/m/Y'), 'd/m/Y') ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-2">
                <label for="cep">CEP</label>
                <input type="text" class="form-control" name="cep" id="cep" value="<?php echo isset($set) ? mask('#####-###', $set->cep) : '' ?>" minlength="9" maxlength="9" placeholder="00000-000">
            </div>
            <div class="form-group col-md">
                <label for="endereco">ENDEREÇO</label>
                <input type="text" class="form-control" name="endereco" id="endereco" value="<?php echo isset($set) ? $set->endereco : '' ?>" maxlength="255">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col">
                <label for="">SITUAÇÃO</label>
                <br/>
                <label><input type="radio" value="1" name="situacao" <?php echo !isset($set) || (isset($set) && $set->situacao == 1) ? 'checked' : '' ?>> Ativo</label>
                <label><input type="radio" value="0" name="situacao" <?php echo isset($set) && $set->situacao == 0 ? 'checked' : '' ?>> Inativo</label>
            </div>
        </div>
        <button type="submit" class="btn btn-success">SALVAR</button>
        <a href="escolas.php" class="btn btn-dark btn-xs">CANCELAR</a>
    </form>
<?php }
else
{
    $sql = "";
    if (isset($_GET['nome']) && !empty($_GET['nome']))
        $sql .= " AND nome LIKE :nome ";
    
    if (isset($_GET['data']) && !empty($_GET['data']))
        $sql .= " AND data = :data ";
    
    if (isset($_GET['cep']) && !empty($_GET['cep']))
        $sql .= " AND cep = :cep ";
    
    if (isset($_GET['endereco']) && !empty($_GET['endereco']))
        $sql .= " AND endereco LIKE :endereco ";

    if (isset($_GET['situacao']) && $_GET['situacao'] != '')
        $sql .= " AND situacao = :situacao ";

    $stm = $connect->prepare("SELECT * FROM escolas WHERE 1 = 1 $sql ORDER BY id DESC");

    if (isset($_GET['nome']) && !empty($_GET['nome']))
        $stm->bindValue(':nome', '%' . trim($_GET['nome']) . '%', PDO::PARAM_STR);
    
    if (isset($_GET['data']) && !empty($_GET['data']))
        $stm->bindValue(':data', formataData($_GET['data'], 'Y-m-d'), PDO::PARAM_STR);

    if (isset($_GET['cep']) && !empty($_GET['cep']))
        $stm->bindValue(':cep', limpar(trim($_GET['cep'])), PDO::PARAM_INT);

    if (isset($_GET['endereco']) && !empty($_GET['endereco']))
        $stm->bindValue(':endereco', '%' . $_GET['endereco'] . '%', PDO::PARAM_STR);
    
    if (isset($_GET['situacao']) && $_GET['situacao'] != '')
        $stm->bindValue(':situacao', $_GET['situacao'], PDO::PARAM_STR);

    $stm->execute();
    $sets = $stm->fetchAll(PDO::FETCH_OBJ);
    ?>
    <h1 class="h3 mb-3">
        ESCOLAS <small>TOTAL <?php echo count($sets) ?></small>
        <a href="?new" class="btn btn-primary btn-xs float-right">NOVO</a>
    </h1>

    <div class="card mb-3">
        <div class="card-header">FILTROS DE PESQUISA</div>
        <form class="card-body">
            <div class="form-row">
                <div class="form-group col-md">
                    <label for="nome">Nome</label>
                    <input type="nome" class="form-control" name="nome" id="nome" value="<?php echo isset($_GET['nome']) ? $_GET['nome'] : '' ?>">
                </div>
                <div class="form-group col-md-2">
                    <label for="data">Data</label>
                    <input type="date" class="form-control" name="data" id="data" value="<?php echo isset($_GET['data']) ? $_GET['data'] : '' ?>">
                </div>
                <div class="form-group col-md-2">
                    <label for="cep">CEP</label>
                    <input type="text" class="form-control" name="cep" id="cep" value="<?php echo isset($_GET['cep']) ? $_GET['cep'] : '' ?>" minlength="9" maxlength="9" placeholder="00000-000">
                </div>
                <div class="form-group col-md">
                    <label for="endereco">ENDEREÇO</label>
                    <input type="text" class="form-control" name="endereco" id="endereco" value="<?php echo isset($_GET['endereco']) ? $_GET['endereco'] : '' ?>" maxlength="255">
                </div>
                <div class="form-group col-md-auto">
                    <label for="">SITUAÇÃO</label>
                    <br/>
                    <label><input type="radio" value="1" name="situacao" <?php echo isset($_GET['situacao']) && $_GET['situacao'] == 1 ? 'checked' : '' ?>> Ativo</label>
                    <label><input type="radio" value="0" name="situacao" <?php echo isset($_GET['situacao']) && $_GET['situacao'] == 0 ? 'checked' : '' ?>> Inativo</label>
                </div>
                <div class="form-group col-md-auto">
                    <div class="w-100"><label for="">&nbsp;</label></div>
                    <button type="submit" class="btn btn-primary">Pesquisar</button>
                </div>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover table-dark">
            <thead>
                <tr>
                    <th class="text-right">ID</th>
                    <th>Nome</th>
                    <th>Data</th>
                    <th>CEP</th>
                    <th>Endereço</th>
                    <th>Situação</th>
                    <th style="width: 1%;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sets as $set) { ?>
                    <tr>
                        <th class="text-right"><?php echo $set->id ?></th>
                        <td><?php echo $set->nome ?></td>
                        <td><?php echo formataData($set->data, 'd/m/Y') ?></td>
                        <td><?php echo $set->cep ? mask('#####-###', $set->cep) : '' ?></td>
                        <td><?php echo $set->endereco ?></td>
                        <td>
                            <span class="badge badge-pill badge-<?php echo $set->situacao == 1 ? 'success' : 'danger' ?>"><?php echo $set->situacao ? 'Ativo' : 'Inativo' ?></span>
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
