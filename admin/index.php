<?php
// Include database configuration
require_once '../config.php';

// Initialize session
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit();
}

// Get statistics
$stats = array();

// Total products
$sql = "SELECT COUNT(*) as total FROM produtos";
$result = $conn->query($sql);
$stats['total_produtos'] = $result->fetch_assoc()['total'];

// Total categories
$sql = "SELECT COUNT(*) as total FROM categorias";
$result = $conn->query($sql);
$stats['total_categorias'] = $result->fetch_assoc()['total'];

// Total users
$sql = "SELECT COUNT(*) as total FROM usuarios";
$result = $conn->query($sql);
$stats['total_usuarios'] = $result->fetch_assoc()['total'];

// Total orders
$sql = "SELECT COUNT(*) as total FROM pedidos";
$result = $conn->query($sql);
$stats['total_pedidos'] = $result->fetch_assoc()['total'];

// Recent orders
$sql = "SELECT p.*, u.nome as cliente_nome 
        FROM pedidos p 
        JOIN usuarios u ON p.usuario_id = u.id 
        ORDER BY p.data_pedido DESC 
        LIMIT 5";
$recent_orders = $conn->query($sql);

// Low stock products
$sql = "SELECT * FROM produtos WHERE estoque <= 5 ORDER BY estoque ASC LIMIT 5";
$low_stock = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <title>Dashboard - EShopper Admin</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">

    <!-- Favicon -->
    <link href="../img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet"> 

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="../css/style.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar Start -->
    <div class="container-fluid">
        <div class="row border-top px-xl-5">
            <div class="col-lg-12">
                <nav class="navbar navbar-expand-lg bg-light navbar-light py-3 py-lg-0 px-0">
                    <a href="" class="text-decoration-none d-block d-lg-none">
                        <h1 class="m-0 display-5 font-weight-semi-bold"><span class="text-primary font-weight-bold border px-3 mr-1">E</span>Shopper</h1>
                    </a>
                    <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
                        <div class="navbar-nav mr-auto py-0">
                            <a href="dashboard.php" class="nav-item nav-link active">Dashboard</a>
                            <a href="produtos/index.php" class="nav-item nav-link">Produtos</a>
                            <a href="categorias/index.php" class="nav-item nav-link">Categorias</a>
                            <a href="vendas/index.php" class="nav-item nav-link">Vendas</a>
                        </div>
                        <div class="navbar-nav ml-auto py-0">
                            <div class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-user text-primary"></i> <?php echo htmlspecialchars($_SESSION['user_nome']); ?>
                                </a>
                                <div class="dropdown-menu rounded-0 m-0">
                                    <a href="../index.php" class="dropdown-item">Ver Site</a>
                                    <a href="logout.php" class="dropdown-item">Sair</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
    <!-- Navbar End -->

    <!-- Dashboard Start -->
    <div class="container-fluid pt-5">
        <div class="row px-xl-5">
            <!-- Statistics Cards -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-secondary h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="font-weight-bold">Total de Produtos</h6>
                                <h2 class="mb-0"><?php echo $stats['total_produtos']; ?></h2>
                            </div>
                            <i class="fa fa-box fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-secondary h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="font-weight-bold">Total de Categorias</h6>
                                <h2 class="mb-0"><?php echo $stats['total_categorias']; ?></h2>
                            </div>
                            <i class="fa fa-tags fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-secondary h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="font-weight-bold">Total de Usuários</h6>
                                <h2 class="mb-0"><?php echo $stats['total_usuarios']; ?></h2>
                            </div>
                            <i class="fa fa-users fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-secondary h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="font-weight-bold">Total de Pedidos</h6>
                                <h2 class="mb-0"><?php echo $stats['total_pedidos']; ?></h2>
                            </div>
                            <i class="fa fa-shopping-cart fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="col-lg-8 mb-4">
                <div class="card border-secondary">
                    <div class="card-header bg-secondary border-0">
                        <h4 class="font-weight-semi-bold m-0 text-white">Pedidos Recentes</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($order = $recent_orders->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $order['id']; ?></td>
                                        <td><?php echo htmlspecialchars($order['cliente_nome']); ?></td>
                                        <td>R$ <?php echo number_format($order['total'], 2, ',', '.'); ?></td>
                                        <td>
                                            <span class="badge badge-<?php 
                                                echo $order['status'] === 'pendente' ? 'warning' : 
                                                    ($order['status'] === 'aprovado' ? 'success' : 
                                                    ($order['status'] === 'cancelado' ? 'danger' : 'info')); 
                                            ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($order['data_pedido'])); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Low Stock Products -->
            <div class="col-lg-4 mb-4">
                <div class="card border-secondary">
                    <div class="card-header bg-secondary border-0">
                        <h4 class="font-weight-semi-bold m-0 text-white">Produtos com Estoque Baixo</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Produto</th>
                                        <th>Estoque</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($product = $low_stock->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['nome']); ?></td>
                                        <td>
                                            <span class="badge badge-danger"><?php echo $product['estoque']; ?></span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Dashboard End -->

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
</body>
</html> 