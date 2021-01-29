<?php
require_once 'utils/conn.php';


require_once 'utils/header.php';
require_once 'utils/menu.php'; ?>

<div class="row">
    <!-- TOTAL DE ESCOLAS -->
    <div class="col">
        <div class="card text-white bg-primary mb-3">
            <div class="card-header text-center h5">TOTAL DE ESCOLAS</div>
            <div class="card-body text-center">
                <p class="h1"><?php echo $connect->query('SELECT count(id) FROM escolas')->fetchColumn(); ?></p>
            </div>
        </div>
    </div>

    <!-- TOTAL DE TURMAS -->
    <div class="col">
        <div class="card text-white bg-primary mb-3">
            <div class="card-header text-center h5">TOTAL DE TURMAS</div>
            <div class="card-body text-center">
                <p class="h1"><?php echo $connect->query('SELECT count(id) FROM turmas')->fetchColumn(); ?></p>
            </div>
        </div>
    </div>

    <!-- TOTAL DE ALUNO -->
    <div class="col">
        <div class="card text-white bg-primary mb-3">
            <div class="card-header text-center h5">TOTAL DE ALUNOS</div>
            <div class="card-body text-center">
                <p class="h1"><?php echo $connect->query('SELECT count(id) FROM alunos')->fetchColumn(); ?></p>
            </div>
        </div>
    </div>
</div>


<?php require_once 'utils/footer.php';
