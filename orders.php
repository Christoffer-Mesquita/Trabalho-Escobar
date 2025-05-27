<?php
// Include authentication check
require_once 'auth.php';

// Include database configuration
require_once 'config.php';

// Get user orders
$sql = "SELECT p.*, 
        (SELECT COUNT(*) FROM pedidos_itens WHERE pedido_id = p.id) as total_itens,
        (SELECT SUM(quantidade * preco_unitario) FROM pedidos_itens WHERE pedido_id = p.id) as valor_total
        FROM pedidos p 
        WHERE usuario_id = ? 
        ORDER BY data_pedido DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <title>Meus Pedidos - EShopper</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet"> 

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
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
                <form action="">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Buscar produtos">
                        <div class="input-group-append">
                            <span class="input-group-text bg-transparent text-primary">
                                <i class="fa fa-search"></i>
                            </span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-3 col-6 text-right">
                <a href="" class="btn border">
                    <i class="fas fa-heart text-primary"></i>
                    <span class="badge">0</span>
                </a>
                <a href="cart.php" class="btn border">
                    <i class="fas fa-shopping-cart text-primary"></i>
                    <span class="badge">0</span>
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
                            <a href="index.php" class="nav-item nav-link">Home</a>
                            <a href="shop.php" class="nav-item nav-link">Loja</a>
                            <a href="detail.php" class="nav-item nav-link">Detalhes</a>
                            <a href="cart.php" class="nav-item nav-link">Carrinho</a>
                            <a href="checkout.php" class="nav-item nav-link">Checkout</a>
                            <a href="contact.php" class="nav-item nav-link">Contato</a>
                        </div>
                        <div class="navbar-nav ml-auto py-0">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <div class="nav-item dropdown">
                                    <a href="#" class="nav-link dropdown-toggle active" data-toggle="dropdown">
                                        <i class="fa fa-user text-primary"></i> <?php echo htmlspecialchars($_SESSION['user_nome']); ?>
                                    </a>
                                    <div class="dropdown-menu rounded-0 m-0">
                                        <a href="profile.php" class="dropdown-item">Meu Perfil</a>
                                        <a href="orders.php" class="dropdown-item active">Meus Pedidos</a>
                                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                                            <a href="admin/index.php" class="dropdown-item">Painel Admin</a>
                                        <?php endif; ?>
                                        <a href="logout.php" class="dropdown-item">Sair</a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <a href="login.php" class="nav-item nav-link">Login</a>
                                <a href="register.php" class="nav-item nav-link">Registrar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
    <!-- Navbar End -->

    <!-- Orders Start -->
    <div class="container-fluid pt-5">
        <div class="row px-xl-5">
            <div class="col-lg-12">
                <div class="card border-secondary mb-5">
                    <div class="card-header bg-secondary border-0">
                        <h4 class="font-weight-semi-bold m-0">Meus Pedidos</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($orders->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered text-center mb-0">
                                    <thead class="bg-secondary text-dark">
                                        <tr>
                                            <th>Pedido #</th>
                                            <th>Data</th>
                                            <th>Status</th>
                                            <th>Itens</th>
                                            <th>Total</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody class="align-middle">
                                        <?php while ($order = $orders->fetch_assoc()): ?>
                                            <tr>
                                                <td class="align-middle"><?php echo str_pad($order['id'], 8, '0', STR_PAD_LEFT); ?></td>
                                                <td class="align-middle"><?php echo date('d/m/Y H:i', strtotime($order['data_pedido'])); ?></td>
                                                <td class="align-middle">
                                                    <?php
                                                    $status_class = '';
                                                    switch ($order['status']) {
                                                        case 'pendente':
                                                            $status_class = 'warning';
                                                            break;
                                                        case 'aprovado':
                                                            $status_class = 'info';
                                                            break;
                                                        case 'enviado':
                                                            $status_class = 'primary';
                                                            break;
                                                        case 'entregue':
                                                            $status_class = 'success';
                                                            break;
                                                        case 'cancelado':
                                                            $status_class = 'danger';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge badge-<?php echo $status_class; ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="align-middle"><?php echo $order['total_itens']; ?> itens</td>
                                                <td class="align-middle">R$ <?php echo number_format($order['valor_total'], 2, ',', '.'); ?></td>
                                                <td class="align-middle">
                                                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#orderModal<?php echo $order['id']; ?>">
                                                        Detalhes
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Order Details Modal -->
                                            <div class="modal fade" id="orderModal<?php echo $order['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="orderModalLabel<?php echo $order['id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog modal-lg" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="orderModalLabel<?php echo $order['id']; ?>">
                                                                Pedido #<?php echo str_pad($order['id'], 8, '0', STR_PAD_LEFT); ?>
                                                            </h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <?php
                                                            // Get order items
                                                            $items_sql = "SELECT pi.*, p.nome as produto_nome, c.nome as categoria_nome 
                                                                        FROM pedidos_itens pi 
                                                                        JOIN produtos p ON pi.produto_id = p.id 
                                                                        JOIN categorias c ON p.categoria_id = c.id 
                                                                        WHERE pi.pedido_id = ?";
                                                            $items_stmt = $conn->prepare($items_sql);
                                                            $items_stmt->bind_param("i", $order['id']);
                                                            $items_stmt->execute();
                                                            $items = $items_stmt->get_result();
                                                            ?>
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered">
                                                                    <thead class="bg-secondary text-dark">
                                                                        <tr>
                                                                            <th>Produto</th>
                                                                            <th>Categoria</th>
                                                                            <th>Quantidade</th>
                                                                            <th>Preço Unit.</th>
                                                                            <th>Subtotal</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php while ($item = $items->fetch_assoc()): ?>
                                                                            <tr>
                                                                                <td><?php echo htmlspecialchars($item['produto_nome']); ?></td>
                                                                                <td><?php echo htmlspecialchars($item['categoria_nome']); ?></td>
                                                                                <td><?php echo $item['quantidade']; ?></td>
                                                                                <td>R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                                                                                <td>R$ <?php echo number_format($item['quantidade'] * $item['preco_unitario'], 2, ',', '.'); ?></td>
                                                                            </tr>
                                                                        <?php endwhile; ?>
                                                                    </tbody>
                                                                    <tfoot>
                                                                        <tr>
                                                                            <td colspan="4" class="text-right"><strong>Total:</strong></td>
                                                                            <td><strong>R$ <?php echo number_format($order['valor_total'], 2, ',', '.'); ?></strong></td>
                                                                        </tr>
                                                                    </tfoot>
                                                                </table>
                                                            </div>

                                                            <div class="row mt-4">
                                                                <div class="col-md-6">
                                                                    <h6>Endereço de Entrega</h6>
                                                                    <p>
                                                                        <?php echo nl2br(htmlspecialchars($order['endereco_entrega'])); ?>
                                                                    </p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <h6>Informações do Pedido</h6>
                                                                    <p>
                                                                        <strong>Data do Pedido:</strong> 
                                                                        <?php echo date('d/m/Y H:i', strtotime($order['data_pedido'])); ?><br>
                                                                        <strong>Status:</strong> 
                                                                        <span class="badge badge-<?php echo $status_class; ?>">
                                                                            <?php echo ucfirst($order['status']); ?>
                                                                        </span><br>
                                                                        <?php if (!empty($order['codigo_rastreio'])): ?>
                                                                            <strong>Código de Rastreio:</strong> 
                                                                            <?php echo htmlspecialchars($order['codigo_rastreio']); ?>
                                                                        <?php endif; ?>
                                                                    </p>
                                                                </div>
                                                            </div>
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
                        <?php else: ?>
                            <div class="text-center">
                                <p class="mb-0">Você ainda não realizou nenhum pedido.</p>
                                <a href="shop.php" class="btn btn-primary mt-3">Ir para a Loja</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Orders End -->

    <!-- Footer Start -->
    <div class="container-fluid bg-secondary text-dark mt-5 pt-5">
        <div class="row px-xl-5 pt-5">
            <div class="col-lg-4 col-md-12 mb-5 pr-3 pr-xl-5">
                <a href="" class="text-decoration-none">
                    <h1 class="mb-4 display-5 font-weight-semi-bold"><span class="text-primary font-weight-bold border border-white px-3 mr-1">E</span>Shopper</h1>
                </a>
                <p>Loja virtual com os melhores produtos e preços do mercado.</p>
                <p class="mb-2"><i class="fa fa-map-marker-alt text-primary mr-3"></i>Rua Exemplo, 123, São Paulo, SP</p>
                <p class="mb-2"><i class="fa fa-envelope text-primary mr-3"></i>contato@eshopper.com</p>
                <p class="mb-0"><i class="fa fa-phone-alt text-primary mr-3"></i>+55 11 99999-9999</p>
            </div>
            <div class="col-lg-8 col-md-12">
                <div class="row">
                    <div class="col-md-4 mb-5">
                        <h5 class="font-weight-bold text-dark mb-4">Links Rápidos</h5>
                        <div class="d-flex flex-column justify-content-start">
                            <a class="text-dark mb-2" href="index.php"><i class="fa fa-angle-right mr-2"></i>Home</a>
                            <a class="text-dark mb-2" href="shop.php"><i class="fa fa-angle-right mr-2"></i>Nossa Loja</a>
                            <a class="text-dark mb-2" href="detail.php"><i class="fa fa-angle-right mr-2"></i>Detalhes</a>
                            <a class="text-dark mb-2" href="cart.php"><i class="fa fa-angle-right mr-2"></i>Carrinho</a>
                            <a class="text-dark mb-2" href="checkout.php"><i class="fa fa-angle-right mr-2"></i>Checkout</a>
                            <a class="text-dark" href="contact.php"><i class="fa fa-angle-right mr-2"></i>Contato</a>
                        </div>
                    </div>
                    <div class="col-md-4 mb-5">
                        <h5 class="font-weight-bold text-dark mb-4">Categorias</h5>
                        <div class="d-flex flex-column justify-content-start">
                            <?php
                            $categories = $conn->query("SELECT * FROM categorias ORDER BY nome");
                            if ($categories && $categories->num_rows > 0) {
                                while($category = $categories->fetch_assoc()) {
                            ?>
                            <a class="text-dark mb-2" href="shop.php?categoria=<?php echo $category['id']; ?>">
                                <i class="fa fa-angle-right mr-2"></i><?php echo htmlspecialchars($category['nome']); ?>
                            </a>
                            <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4 mb-5">
                        <h5 class="font-weight-bold text-dark mb-4">Newsletter</h5>
                        <form action="">
                            <div class="form-group">
                                <input type="text" class="form-control border-0 py-4" placeholder="Seu Nome" required="required" />
                            </div>
                            <div class="form-group">
                                <input type="email" class="form-control border-0 py-4" placeholder="Seu Email" required="required" />
                            </div>
                            <div>
                                <button class="btn btn-primary btn-block border-0 py-3" type="submit">Inscrever-se</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row border-top border-light mx-xl-5 py-4">
            <div class="col-md-6 px-xl-0">
                <p class="mb-md-0 text-center text-md-left text-dark">
                    &copy; <a class="text-dark font-weight-semi-bold" href="#">EShopper</a>. Todos os direitos reservados.
                </p>
            </div>
            <div class="col-md-6 px-xl-0 text-center text-md-right">
                <img class="img-fluid" src="img/payments.png" alt="">
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary back-to-top"><i class="fa fa-angle-double-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>
</html> 