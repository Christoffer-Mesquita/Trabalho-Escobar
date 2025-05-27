<?php
require_once 'config.php';
session_start();

// Verifica se há um pedido de sucesso
if (!isset($_SESSION['pedido_sucesso'])) {
    header('Location: index.php');
    exit;
}

$pedido_id = $_SESSION['pedido_sucesso'];

// Busca informações do pedido
$stmt = $conn->prepare("
    SELECT p.*, u.nome as usuario_nome, u.email as usuario_email 
    FROM pedidos p 
    JOIN usuarios u ON p.usuario_id = u.id 
    WHERE p.id = ?
");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();

if (!$pedido) {
    header('Location: index.php');
    exit;
}

// Busca os itens do pedido
$stmt = $conn->prepare("
    SELECT pi.*, pr.nome as produto_nome 
    FROM pedidos_itens pi 
    JOIN produtos pr ON pi.produto_id = pr.id 
    WHERE pi.pedido_id = ?
");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$itens = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Limpa a sessão de pedido sucesso
unset($_SESSION['pedido_sucesso']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado - EShopper</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Breadcrumb Start -->
    <div class="container-fluid">
        <div class="row px-xl-5">
            <div class="col-12">
                <nav class="breadcrumb bg-light mb-30">
                    <a class="breadcrumb-item text-dark" href="index.php">Home</a>
                    <a class="breadcrumb-item text-dark" href="cart.php">Carrinho</a>
                    <a class="breadcrumb-item text-dark" href="checkout.php">Checkout</a>
                    <span class="breadcrumb-item active">Pedido Confirmado</span>
                </nav>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->

    <!-- Success Start -->
    <div class="container-fluid pt-5">
        <div class="row px-xl-5">
            <div class="col-lg-8 mx-auto">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h2 class="mb-4">Pedido Confirmado!</h2>
                        <p class="text-muted mb-4">Seu pedido foi recebido e está sendo processado.</p>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5 class="font-weight-bold mb-3">Informações do Pedido</h5>
                                <p class="mb-1"><strong>Número do Pedido:</strong> #<?php echo str_pad($pedido['id'], 8, '0', STR_PAD_LEFT); ?></p>
                                <p class="mb-1"><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></p>
                                <p class="mb-1"><strong>Status:</strong> <span class="badge bg-success">Aprovado</span></p>
                                <p class="mb-1"><strong>Total:</strong> R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h5 class="font-weight-bold mb-3">Informações do Cliente</h5>
                                <p class="mb-1"><strong>Nome:</strong> <?php echo htmlspecialchars($pedido['usuario_nome']); ?></p>
                                <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($pedido['usuario_email']); ?></p>
                                <p class="mb-1"><strong>Endereço de Entrega:</strong><br><?php echo nl2br(htmlspecialchars($pedido['endereco_entrega'])); ?></p>
                            </div>
                        </div>

                        <h5 class="font-weight-bold mb-3">Itens do Pedido</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Produto</th>
                                        <th class="text-center">Quantidade</th>
                                        <th class="text-right">Preço Unit.</th>
                                        <th class="text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($itens as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['produto_nome']); ?></td>
                                            <td class="text-center"><?php echo $item['quantidade']; ?></td>
                                            <td class="text-right">R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                                            <td class="text-right">R$ <?php echo number_format($item['quantidade'] * $item['preco_unitario'], 2, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Total</strong></td>
                                        <td class="text-right"><strong>R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <?php if (!empty($pedido['observacoes'])): ?>
                            <div class="alert alert-info mb-4">
                                <h5 class="font-weight-bold mb-2">Observações do Pedido</h5>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($pedido['observacoes'])); ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="mt-4">
                            <a href="shop.php" class="btn btn-primary mr-2">Continuar Comprando</a>
                            <a href="orders.php" class="btn btn-outline-primary">Ver Meus Pedidos</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Success End -->

    <?php include 'includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 