<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit();
}
?>
<!-- Topbar Start -->
<div class="container-fluid bg-dark">
    <div class="row py-2 px-xl-5">
        <div class="col-lg-6 d-none d-lg-block">
            <div class="d-inline-flex align-items-center">
                <a class="text-light" href="mailto:contato@eshopper.com">contato@eshopper.com</a>
                <span class="text-muted px-2">|</span>
                <a class="text-light" href="tel:+5511999999999">+55 11 99999-9999</a>
            </div>
        </div>
        <div class="col-lg-6 text-center text-lg-right">
            <div class="d-inline-flex align-items-center">
                <a class="text-light px-2" href="../../index.php" target="_blank">
                    <i class="fas fa-store"></i> Ver Loja
                </a>
                <span class="text-muted px-2">|</span>
                <a class="text-light px-2" href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </div>
    </div>
</div>
<!-- Topbar End -->

<!-- Navbar Start -->
<div class="container-fluid">
    <div class="row border-top px-xl-5">
        <div class="col-lg-3 d-none d-lg-block">
            <a class="btn shadow-none d-flex align-items-center justify-content-between bg-primary text-white w-100" data-toggle="collapse" href="#navbar-vertical" style="height: 65px; margin-top: -1px; padding: 0 30px;">
                <h6 class="m-0">Menu</h6>
                <i class="fa fa-angle-down text-dark"></i>
            </a>
            <nav class="collapse position-absolute navbar navbar-vertical navbar-light align-items-start p-0 border border-top-0 border-bottom-0 bg-light" id="navbar-vertical" style="width: calc(100% - 30px); z-index: 1;">
                <div class="navbar-nav w-100 overflow-hidden" style="height: 410px">
                    <a href="../dashboard.php" class="nav-item nav-link">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="../produtos/index.php" class="nav-item nav-link">
                        <i class="fas fa-box"></i> Produtos
                    </a>
                    <a href="../categorias/index.php" class="nav-item nav-link">
                        <i class="fas fa-tags"></i> Categorias
                    </a>
                    <a href="../pedidos/index.php" class="nav-item nav-link">
                        <i class="fas fa-shopping-cart"></i> Pedidos
                    </a>
                    <a href="../usuarios/index.php" class="nav-item nav-link">
                        <i class="fas fa-users"></i> Usuários
                    </a>
                    <a href="../relatorios/index.php" class="nav-item nav-link">
                        <i class="fas fa-chart-bar"></i> Relatórios
                    </a>
                    <a href="../configuracoes/index.php" class="nav-item nav-link">
                        <i class="fas fa-cog"></i> Configurações
                    </a>
                </div>
            </nav>
        </div>
        <div class="col-lg-9">
            <nav class="navbar navbar-expand-lg bg-light navbar-light py-3 py-lg-0 px-0">
                <a href="../dashboard.php" class="text-decoration-none d-block d-lg-none">
                    <h1 class="m-0 display-5 font-weight-semi-bold"><span class="text-primary font-weight-bold border px-3 mr-1">E</span>Shopper</h1>
                </a>
                <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
                    <div class="navbar-nav mr-auto py-0">
                        <a href="../dashboard.php" class="nav-item nav-link">Dashboard</a>
                        <a href="../produtos/index.php" class="nav-item nav-link">Produtos</a>
                        <a href="../categorias/index.php" class="nav-item nav-link">Categorias</a>
                        <a href="../pedidos/index.php" class="nav-item nav-link">Pedidos</a>
                        <a href="../usuarios/index.php" class="nav-item nav-link">Usuários</a>
                        <a href="../relatorios/index.php" class="nav-item nav-link">Relatórios</a>
                        <a href="../configuracoes/index.php" class="nav-item nav-link">Configurações</a>
                    </div>
                </div>
            </nav>
        </div>
    </div>
</div>
<!-- Navbar End -->

<?php if (isset($_SESSION['success'])): ?>
    <div class="container mt-3">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="container mt-3">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
<?php endif; ?> 