<?php
// Include database configuration
require_once '../../config.php';

// Initialize session
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../login.php');
    exit();
}

// Process order actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    
    if ($order_id) {
        switch ($_POST['action']) {
            case 'update_status':
                $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
                if (in_array($status, ['pendente', 'aprovado', 'enviado', 'entregue', 'cancelado'])) {
                    $sql = "UPDATE pedidos SET status = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $status, $order_id);
                    $stmt->execute();
                }
                break;

            case 'delete':
                // Check if order can be deleted (only pending orders)
                $check_sql = "SELECT status FROM pedidos WHERE id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("i", $order_id);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                $order = $result->fetch_assoc();

                if ($order && $order['status'] === 'pendente') {
                    // Delete order items first
                    $sql = "DELETE FROM pedidos_itens WHERE pedido_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $order_id);
                    $stmt->execute();

                    // Then delete the order
                    $sql = "DELETE FROM pedidos WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $order_id);
                    $stmt->execute();
                }
                break;
        }
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_start = isset($_GET['date_start']) ? $_GET['date_start'] : '';
$date_end = isset($_GET['date_end']) ? $_GET['date_end'] : '';

// Build query
$sql = "SELECT p.*, u.nome as cliente_nome, u.email as cliente_email,
        (SELECT COUNT(*) FROM pedidos_itens WHERE pedido_id = p.id) as total_itens
        FROM pedidos p 
        JOIN usuarios u ON p.usuario_id = u.id 
        WHERE 1=1";
$params = array();
$types = "";

if ($status_filter) {
    $sql .= " AND p.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if ($date_start) {
    $sql .= " AND DATE(p.data_pedido) >= ?";
    $params[] = $date_start;
    $types .= "s";
}

if ($date_end) {
    $sql .= " AND DATE(p.data_pedido) <= ?";
    $params[] = $date_end;
    $types .= "s";
}

$sql .= " ORDER BY p.data_pedido DESC";

// Prepare and execute query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$orders = $stmt->get_result();

// Get order statistics
$stats = array();

// Total orders
$sql = "SELECT COUNT(*) as total FROM pedidos";
$result = $conn->query($sql);
$stats['total_pedidos'] = $result->fetch_assoc()['total'];

// Total revenue
$sql = "SELECT SUM(total) as total FROM pedidos WHERE status != 'cancelado'";
$result = $conn->query($sql);
$stats['total_vendas'] = $result->fetch_assoc()['total'] ?? 0;

// Orders by status
$sql = "SELECT status, COUNT(*) as total FROM pedidos GROUP BY status";
$result = $conn->query($sql);
$stats['por_status'] = array();
while ($row = $result->fetch_assoc()) {
    $stats['por_status'][$row['status']] = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <title>Gerenciar Vendas - EShopper Admin</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">

    <!-- Favicon -->
    <link href="../../img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet"> 

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="../../css/style.css" rel="stylesheet">
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
                            <a href="../dashboard.php" class="nav-item nav-link">Dashboard</a>
                            <a href="../produtos/index.php" class="nav-item nav-link">Produtos</a>
                            <a href="../categorias/index.php" class="nav-item nav-link">Categorias</a>
                            <a href="index.php" class="nav-item nav-link active">Vendas</a>
                        </div>
                        <div class="navbar-nav ml-auto py-0">
                            <div class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-user text-primary"></i> <?php echo htmlspecialchars($_SESSION['user_nome']); ?>
                                </a>
                                <div class="dropdown-menu rounded-0 m-0">
                                    <a href="../../index.php" class="dropdown-item">Ver Site</a>
                                    <a href="../logout.php" class="dropdown-item">Sair</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
    <!-- Navbar End -->

    <!-- Sales Start -->
    <div class="container-fluid pt-5">
        <div class="row px-xl-5">
            <!-- Statistics Cards -->
            <div class="col-lg-4 col-md-6 mb-4">
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
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card border-secondary h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="font-weight-bold">Total de Vendas</h6>
                                <h2 class="mb-0">R$ <?php echo number_format($stats['total_vendas'], 2, ',', '.'); ?></h2>
                            </div>
                            <i class="fa fa-dollar-sign fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card border-secondary h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="font-weight-bold">Pedidos Pendentes</h6>
                                <h2 class="mb-0"><?php echo $stats['por_status']['pendente'] ?? 0; ?></h2>
                            </div>
                            <i class="fa fa-clock fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="col-lg-12 mb-4">
                <div class="card border-secondary">
                    <div class="card-header bg-secondary border-0">
                        <h4 class="font-weight-semi-bold m-0 text-white">Filtrar Pedidos</h4>
                    </div>
                    <div class="card-body">
                        <form action="" method="GET" class="form-row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" name="status">
                                        <option value="">Todos</option>
                                        <option value="pendente" <?php echo $status_filter === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                        <option value="aprovado" <?php echo $status_filter === 'aprovado' ? 'selected' : ''; ?>>Aprovado</option>
                                        <option value="enviado" <?php echo $status_filter === 'enviado' ? 'selected' : ''; ?>>Enviado</option>
                                        <option value="entregue" <?php echo $status_filter === 'entregue' ? 'selected' : ''; ?>>Entregue</option>
                                        <option value="cancelado" <?php echo $status_filter === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Data Inicial</label>
                                    <input type="date" class="form-control" name="date_start" value="<?php echo $date_start; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Data Final</label>
                                    <input type="date" class="form-control" name="date_end" value="<?php echo $date_end; ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">Filtrar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="col-lg-12">
                <div class="card border-secondary">
                    <div class="card-header bg-secondary border-0">
                        <h4 class="font-weight-semi-bold m-0 text-white">Lista de Pedidos</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Data</th>
                                        <th>Total</th>
                                        <th>Itens</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($order = $orders->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $order['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($order['cliente_nome']); ?></strong><br>
                                            <small><?php echo htmlspecialchars($order['cliente_email']); ?></small>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($order['data_pedido'])); ?></td>
                                        <td>R$ <?php echo number_format($order['total'], 2, ',', '.'); ?></td>
                                        <td><?php echo $order['total_itens']; ?></td>
                                        <td>
                                            <form action="" method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <select class="form-control form-control-sm" name="status" onchange="this.form.submit()">
                                                    <option value="pendente" <?php echo $order['status'] === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                                    <option value="aprovado" <?php echo $order['status'] === 'aprovado' ? 'selected' : ''; ?>>Aprovado</option>
                                                    <option value="enviado" <?php echo $order['status'] === 'enviado' ? 'selected' : ''; ?>>Enviado</option>
                                                    <option value="entregue" <?php echo $order['status'] === 'entregue' ? 'selected' : ''; ?>>Entregue</option>
                                                    <option value="cancelado" <?php echo $order['status'] === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#orderModal<?php echo $order['id']; ?>">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            <?php if ($order['status'] === 'pendente'): ?>
                                            <form action="" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este pedido?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <!-- Order Details Modal -->
                                    <div class="modal fade" id="orderModal<?php echo $order['id']; ?>" tabindex="-1" role="dialog">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Detalhes do Pedido #<?php echo $order['id']; ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <?php
                                                    // Get order items
                                                    $sql = "SELECT pi.*, p.nome as produto_nome, p.preco as produto_preco 
                                                            FROM pedidos_itens pi 
                                                            JOIN produtos p ON pi.produto_id = p.id 
                                                            WHERE pi.pedido_id = ?";
                                                    $stmt = $conn->prepare($sql);
                                                    $stmt->bind_param("i", $order['id']);
                                                    $stmt->execute();
                                                    $items = $stmt->get_result();
                                                    ?>
                                                    <div class="table-responsive">
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Produto</th>
                                                                    <th>Quantidade</th>
                                                                    <th>Preço Unit.</th>
                                                                    <th>Subtotal</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php while($item = $items->fetch_assoc()): ?>
                                                                <tr>
                                                                    <td><?php echo htmlspecialchars($item['produto_nome']); ?></td>
                                                                    <td><?php echo $item['quantidade']; ?></td>
                                                                    <td>R$ <?php echo number_format($item['produto_preco'], 2, ',', '.'); ?></td>
                                                                    <td>R$ <?php echo number_format($item['quantidade'] * $item['produto_preco'], 2, ',', '.'); ?></td>
                                                                </tr>
                                                                <?php endwhile; ?>
                                                            </tbody>
                                                            <tfoot>
                                                                <tr>
                                                                    <td colspan="3" class="text-right"><strong>Total:</strong></td>
                                                                    <td><strong>R$ <?php echo number_format($order['total'], 2, ',', '.'); ?></strong></td>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>

                                                    <h6 class="mt-4">Informações de Entrega</h6>
                                                    <p>
                                                        <strong>Endereço:</strong><br>
                                                        <?php echo nl2br(htmlspecialchars($order['endereco_entrega'])); ?>
                                                    </p>
                                                    <p>
                                                        <strong>Observações:</strong><br>
                                                        <?php echo nl2br(htmlspecialchars($order['observacoes'] ?? '')); ?>
                                                    </p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Sales End -->

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
</body>
</html> 