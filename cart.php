<?php
require_once 'config.php';

session_start();
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $quantity = isset($_GET['quantidade']) ? (int)$_GET['quantidade'] : 1;

    switch ($action) {
        case 'add':
            if ($product_id > 0) {
                if (isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id] += $quantity;
                } else {
                    $_SESSION['cart'][$product_id] = $quantity;
                }
            }
            break;

        case 'remove':
            if ($product_id > 0 && isset($_SESSION['cart'][$product_id])) {
                unset($_SESSION['cart'][$product_id]);
            }
            break;

        case 'update':
            if ($product_id > 0 && isset($_SESSION['cart'][$product_id])) {
                if ($quantity > 0) {
                    $_SESSION['cart'][$product_id] = $quantity;
                } else {
                    unset($_SESSION['cart'][$product_id]);
                }
            }
            break;

        case 'clear':
            $_SESSION['cart'] = [];
            break;
    }

    header('Location: cart.php');
    exit();
}

$cart_products = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $ids_string = implode(',', $ids);
    
    $sql = "SELECT p.*, c.nome as categoria_nome 
            FROM produtos p 
            JOIN categorias c ON p.categoria_id = c.id 
            WHERE p.id IN ($ids_string)";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($product = $result->fetch_assoc()) {
            $quantity = $_SESSION['cart'][$product['id']];
            $subtotal = $product['preco'] * $quantity;
            $total += $subtotal;
            
            $cart_products[] = [
                'id' => $product['id'],
                'nome' => $product['nome'],
                'preco' => $product['preco'],
                'quantidade' => $quantity,
                'subtotal' => $subtotal,
                'categoria_nome' => $product['categoria_nome'],
                'categoria_id' => $product['categoria_id']
            ];
        }
    }
}

function getProductImage($categoria_id) {
    $images = [
        1 => 'product-1.jpg', // Roupas
        2 => 'product-2.jpg', // Calçados
        3 => 'product-3.jpg'  // Acessórios
    ];
    return isset($images[$categoria_id]) ? $images[$categoria_id] : 'product-1.jpg';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <title>Carrinho - EShopper</title>
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
                    <span class="badge"><?php echo count($_SESSION['cart']); ?></span>
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
                            <a href="cart.php" class="nav-item nav-link active">Carrinho</a>
                            <a href="checkout.php" class="nav-item nav-link">Checkout</a>
                            <a href="contact.php" class="nav-item nav-link">Contato</a>
                        </div>
                        <div class="navbar-nav ml-auto py-0">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <div class="nav-item dropdown">
                                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                                        <i class="fa fa-user text-primary"></i> <?php echo htmlspecialchars($_SESSION['user_nome']); ?>
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

    <!-- Cart Start -->
    <div class="container-fluid pt-5">
        <div class="row px-xl-5">
            <div class="col-lg-9">
                <div class="container-fluid">
                    <div class="row px-xl-5">
                        <div class="col-lg-12 table-responsive mb-5">
                            <?php if (!empty($cart_products)): ?>
                            <table class="table table-bordered text-center mb-0">
                                <thead class="bg-secondary text-dark">
                                    <tr>
                                        <th>Produtos</th>
                                        <th>Preço</th>
                                        <th>Quantidade</th>
                                        <th>Total</th>
                                        <th>Remover</th>
                                    </tr>
                                </thead>
                                <tbody class="align-middle">
                                    <?php foreach ($cart_products as $item): ?>
                                    <tr>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <img src="img/<?php echo getProductImage($item['categoria_id']); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['nome']); ?>" 
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                                <div class="ml-3">
                                                    <h6 class="text-truncate"><?php echo htmlspecialchars($item['nome']); ?></h6>
                                                    <small class="text-muted"><?php echo htmlspecialchars($item['categoria_nome']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                                        <td class="align-middle">
                                            <div class="input-group quantity mx-auto" style="width: 100px;">
                                                <div class="input-group-btn">
                                                    <button type="button" class="btn btn-sm btn-primary btn-minus" onclick="updateQuantity(<?php echo $item['id']; ?>, -1)">
                                                        <i class="fa fa-minus"></i>
                                                    </button>
                                                </div>
                                                <input type="text" class="form-control form-control-sm bg-secondary text-center" value="<?php echo $item['quantidade']; ?>" readonly>
                                                <div class="input-group-btn">
                                                    <button type="button" class="btn btn-sm btn-primary btn-plus" onclick="updateQuantity(<?php echo $item['id']; ?>, 1)">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
                                        <td class="align-middle">
                                            <a href="cart.php?action=remove&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fa fa-times"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div class="text-right mt-3">
                                <a href="cart.php?action=clear" class="btn btn-danger">Limpar Carrinho</a>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-5">
                                <h3>Seu carrinho está vazio</h3>
                                <p>Adicione produtos ao seu carrinho para continuar comprando.</p>
                                <a href="shop.php" class="btn btn-primary">Continuar Comprando</a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card border-secondary mb-5">
                    <div class="card-header bg-secondary border-0">
                        <h4 class="font-weight-semi-bold m-0">Resumo do Pedido</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3 pt-1">
                            <h6 class="font-weight-medium">Subtotal</h6>
                            <h6 class="font-weight-medium">R$ <?php echo number_format($total, 2, ',', '.'); ?></h6>
                        </div>
                        <div class="d-flex justify-content-between">
                            <h6 class="font-weight-medium">Frete</h6>
                            <h6 class="font-weight-medium">Grátis</h6>
                        </div>
                    </div>
                    <div class="card-footer border-secondary bg-transparent">
                        <div class="d-flex justify-content-between mt-2">
                            <h5 class="font-weight-bold">Total</h5>
                            <h5 class="font-weight-bold">R$ <?php echo number_format($total, 2, ',', '.'); ?></h5>
                        </div>
                        <?php if (!empty($cart_products)): ?>
                        <a href="checkout.php" class="btn btn-block btn-primary my-3 py-3">Finalizar Compra</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Cart End -->

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
    <script>
    function updateQuantity(productId, change) {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('action', 'update');
        currentUrl.searchParams.set('id', productId);
        
        // Get current quantity from the input
        const input = document.querySelector(`input[value][onclick*="${productId}"]`).closest('.quantity').querySelector('input');
        const currentQuantity = parseInt(input.value);
        const newQuantity = currentQuantity + change;
        
        if (newQuantity > 0) {
            currentUrl.searchParams.set('quantidade', newQuantity);
            window.location.href = currentUrl.toString();
        }
    }
    </script>
</body>
</html> 