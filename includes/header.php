<?php
// Inicializa a sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- Topbar Start -->
<div class="container-fluid">
    <div class="row bg-secondary py-2 px-xl-5">
        <div class="col-lg-6 d-none d-lg-block">
            <div class="d-inline-flex align-items-center">
                <a class="text-dark" href="">FAQs</a>
                <span class="text-muted px-2">|</span>
                <a class="text-dark" href="">Ajuda</a>
                <span class="text-muted px-2">|</span>
                <a class="text-dark" href="">Suporte</a>
            </div>
        </div>
        <div class="col-lg-6 text-center text-lg-right">
            <div class="d-inline-flex align-items-center">
                <a class="text-dark px-2" href="">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a class="text-dark px-2" href="">
                    <i class="fab fa-twitter"></i>
                </a>
                <a class="text-dark px-2" href="">
                    <i class="fab fa-linkedin-in"></i>
                </a>
                <a class="text-dark px-2" href="">
                    <i class="fab fa-instagram"></i>
                </a>
                <a class="text-dark pl-2" href="">
                    <i class="fab fa-youtube"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="row align-items-center py-3 px-xl-5">
        <div class="col-lg-3 d-none d-lg-block">
            <a href="index.php" class="text-decoration-none">
                <h1 class="m-0 display-5 font-weight-semi-bold"><span class="text-primary font-weight-bold border px-3 mr-1">E</span>Shopper</h1>
            </a>
        </div>
        <div class="col-lg-6 col-6 text-left">
            <form action="shop.php" method="GET">
                <div class="input-group">
                    <input type="text" class="form-control" name="busca" placeholder="Buscar produtos" value="<?php echo isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : ''; ?>">
                    <div class="input-group-append">
                        <button class="input-group-text bg-transparent text-primary" type="submit">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-lg-3 col-6 text-right">
            <a href="wishlist.php" class="btn border">
                <i class="fas fa-heart text-primary"></i>
                <span class="badge">0</span>
            </a>
            <a href="cart.php" class="btn border">
                <i class="fas fa-shopping-cart text-primary"></i>
                <span class="badge"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
            </a>
        </div>
    </div>
</div>
<!-- Topbar End -->

<!-- Navbar Start -->
<div class="container-fluid mb-5">
    <div class="row border-top px-xl-5">
        <div class="col-lg-9">
            <nav class="navbar navbar-expand-lg bg-light navbar-light py-3 py-lg-0 px-0">
                <a href="" class="text-decoration-none d-block d-lg-none">
                    <h1 class="m-0 display-5 font-weight-semi-bold"><span class="text-primary font-weight-bold border px-3 mr-1">E</span>Shopper</h1>
                </a>
                <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
                    <div class="navbar-nav mr-auto py-0">
                        <a href="index.php" class="nav-item nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a>
                        <a href="shop.php" class="nav-item nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'shop.php' ? 'active' : ''; ?>">Loja</a>
                        <a href="cart.php" class="nav-item nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : ''; ?>">Carrinho</a>
                        <a href="checkout.php" class="nav-item nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'checkout.php' ? 'active' : ''; ?>">Checkout</a>
                        <a href="contact.php" class="nav-item nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">Contato</a>
                    </div>
                    <div class="navbar-nav ml-auto py-0">
                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <div class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-user text-primary"></i> <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>
                                </a>
                                <div class="dropdown-menu rounded-0 m-0">
                                    <a href="profile.php" class="dropdown-item">Meu Perfil</a>
                                    <a href="orders.php" class="dropdown-item">Meus Pedidos</a>
                                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                                        <a href="admin/index.php" class="dropdown-item">Painel Admin</a>
                                    <?php endif; ?>
                                    <a href="logout.php" class="dropdown-item">Sair</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="login.php" class="nav-item nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>">Login</a>
                            <a href="register.php" class="nav-item nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : ''; ?>">Registrar</a>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>
        </div>
    </div>
</div>
<!-- Navbar End --> 