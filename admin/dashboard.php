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

// Buscar estatísticas
$stats = array();

// Total de pedidos
$result = $conn->query("SELECT COUNT(*) as total FROM pedidos");
$stats['total_pedidos'] = $result->fetch_assoc()['total'];

// Total de pedidos pendentes
$result = $conn->query("SELECT COUNT(*) as total FROM pedidos WHERE status = 'pendente'");
$stats['pedidos_pendentes'] = $result->fetch_assoc()['total'];

// Total de produtos
$result = $conn->query("SELECT COUNT(*) as total FROM produtos");
$stats['total_produtos'] = $result->fetch_assoc()['total'];

// Total de usuários
$result = $conn->query("SELECT COUNT(*) as total FROM usuarios");
$stats['total_usuarios'] = $result->fetch_assoc()['total'];

// Total de vendas (soma dos valores dos pedidos)
$result = $conn->query("SELECT COALESCE(SUM(valor_total), 0) as total FROM pedidos WHERE status != 'cancelado'");
$stats['total_vendas'] = number_format($result->fetch_assoc()['total'], 2, ',', '.');

// Pedidos recentes
$pedidos_recentes = $conn->query("
    SELECT p.*, u.nome as nome_usuario 
    FROM pedidos p 
    JOIN usuarios u ON p.usuario_id = u.id 
    ORDER BY p.data_pedido DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Produtos mais vendidos
$produtos_mais_vendidos = $conn->query("
    SELECT p.nome, p.preco, SUM(ip.quantidade) as total_vendido
    FROM itens_pedido ip
    JOIN produtos p ON ip.produto_id = p.id
    JOIN pedidos pd ON ip.pedido_id = pd.id
    WHERE pd.status != 'cancelado'
    GROUP BY p.id
    ORDER BY total_vendido DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Vendas por mês (últimos 6 meses)
$vendas_por_mes = $conn->query("
    SELECT 
        DATE_FORMAT(data_pedido, '%Y-%m') as mes,
        COUNT(*) as total_pedidos,
        COALESCE(SUM(valor_total), 0) as valor_total
    FROM pedidos
    WHERE status != 'cancelado'
    AND data_pedido >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(data_pedido, '%Y-%m')
    ORDER BY mes ASC
")->fetch_all(MYSQLI_ASSOC);

// Preparar dados para o gráfico
$meses = array();
$valores = array();
foreach ($vendas_por_mes as $venda) {
    $mes = DateTime::createFromFormat('Y-m', $venda['mes']);
    $meses[] = $mes->format('M/Y');
    $valores[] = $venda['valor_total'];
}
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

    <!-- Libraries Stylesheet -->
    <link href="../lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="../css/style.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="container-fluid pt-5">
        <div class="row px-xl-5">
            <!-- Cards de Estatísticas -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total de Pedidos</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_pedidos']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total de Vendas</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">R$ <?php echo $stats['total_vendas']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Pedidos Pendentes</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['pedidos_pendentes']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Total de Usuários</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_usuarios']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row px-xl-5">
            <!-- Gráfico de Vendas -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Vendas nos Últimos 6 Meses</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="vendasChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Produtos Mais Vendidos -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Produtos Mais Vendidos</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th>Quantidade</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($produtos_mais_vendidos as $produto): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                                        <td><?php echo $produto['total_vendido']; ?></td>
                                        <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pedidos Recentes -->
        <div class="row px-xl-5">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Pedidos Recentes</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Data</th>
                                        <th>Valor</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pedidos_recentes as $pedido): ?>
                                    <tr>
                                        <td>#<?php echo $pedido['id']; ?></td>
                                        <td><?php echo htmlspecialchars($pedido['nome_usuario']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></td>
                                        <td>R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></td>
                                        <td>
                                            <span class="badge badge-<?php 
                                                echo $pedido['status'] === 'pendente' ? 'warning' : 
                                                    ($pedido['status'] === 'aprovado' ? 'success' : 
                                                    ($pedido['status'] === 'cancelado' ? 'danger' : 'info')); 
                                            ?>">
                                                <?php echo ucfirst($pedido['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="pedidos/view.php?id=<?php echo $pedido['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
    // Gráfico de Vendas
    const ctx = document.getElementById('vendasChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($meses); ?>,
            datasets: [{
                label: 'Vendas (R$)',
                data: <?php echo json_encode($valores); ?>,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1,
                fill: false
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    }
                }
            }
        }
    });
    </script>
</body>
</html> 