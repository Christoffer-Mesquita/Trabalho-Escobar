<?php
require_once 'config.php';

function getProductImage($categoria_id) {
    $images = [
        1 => 'product-1.jpg', // Roupas
        2 => 'product-2.jpg', // Calçados
        3 => 'product-3.jpg'  // Acessórios
    ];
    return isset($images[$categoria_id]) ? $images[$categoria_id] : 'product-1.jpg';
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT p.*, c.nome as categoria_nome 
        FROM produtos p 
        JOIN categorias c ON p.categoria_id = c.id 
        WHERE p.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: index.php");
    exit();
}

$sql_related = "SELECT p.*, c.nome as categoria_nome 
                FROM produtos p 
                JOIN categorias c ON p.categoria_id = c.id 
                WHERE p.categoria_id = ? AND p.id != ? 
                LIMIT 4";

$stmt_related = $conn->prepare($sql_related);
$stmt_related->bind_param("ii", $product['categoria_id'], $product_id);
$stmt_related->execute();
$related_products = $stmt_related->get_result();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product['nome']); ?> - EShopper</title>
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
                            <a href="detail.php" class="nav-item nav-link active">Detalhes</a>
                            <a href="cart.php" class="nav-item nav-link">Carrinho</a>
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

    <!-- Shop Detail Start -->
    <div class="container-fluid py-5">
        <div class="row px-xl-5">
            <div class="col-lg-3 d-none d-lg-block">
                <a class="btn shadow-none d-flex align-items-center justify-content-between bg-primary text-white w-100" data-toggle="collapse" href="#navbar-vertical" style="height: 65px; margin-top: -1px; padding: 0 30px;">
                    <h6 class="m-0">Categorias</h6>
                    <i class="fa fa-angle-down text-dark"></i>
                </a>
                <nav class="collapse show navbar navbar-vertical navbar-light align-items-start p-0 border border-top-0 border-bottom-0" id="navbar-vertical">
                    <div class="navbar-nav w-100 overflow-hidden" style="height: 410px">
                        <?php
                        $categories = $conn->query("SELECT * FROM categorias ORDER BY nome");
                        if ($categories && $categories->num_rows > 0) {
                            while($category = $categories->fetch_assoc()) {
                        ?>
                        <a href="shop.php?categoria=<?php echo $category['id']; ?>" class="nav-item nav-link">
                            <?php echo htmlspecialchars($category['nome']); ?>
                        </a>
                        <?php
                            }
                        }
                        ?>
                    </div>
                </nav>
            </div>
            <div class="col-lg-9">
                <div class="row">
                    <div class="col-lg-5 pb-5">
                        <div id="product-carousel" class="carousel slide" data-ride="carousel">
                            <div class="carousel-inner border">
                                <div class="carousel-item active">
                                    <img class="w-100 h-100" src="img/<?php echo getProductImage($product['categoria_id']); ?>" alt="<?php echo htmlspecialchars($product['nome']); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7 pb-5">
                        <h3 class="font-weight-semi-bold"><?php echo htmlspecialchars($product['nome']); ?></h3>
                        <div class="d-flex mb-3">
                            <div class="text-primary mr-2">
                                <small class="fas fa-star"></small>
                                <small class="fas fa-star"></small>
                                <small class="fas fa-star"></small>
                                <small class="fas fa-star-half-alt"></small>
                                <small class="far fa-star"></small>
                            </div>
                            <small class="pt-1">(50 Avaliações)</small>
                        </div>
                        <h3 class="font-weight-semi-bold mb-4">R$ <?php echo number_format($product['preco'], 2, ',', '.'); ?></h3>
                        <p class="mb-4">Categoria: <?php echo htmlspecialchars($product['categoria_nome']); ?></p>
                        <div class="d-flex mb-3">
                            <p class="text-dark font-weight-medium mb-0 mr-3">Quantidade:</p>
                            <form class="d-flex align-items-center" action="cart.php" method="GET">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                <div class="input-group quantity mr-3" style="width: 130px;">
                                    <div class="input-group-btn">
                                        <button type="button" class="btn btn-primary btn-minus">
                                            <i class="fa fa-minus"></i>
                                        </button>
                                    </div>
                                    <input type="number" class="form-control bg-secondary text-center" name="quantidade" value="1" min="1">
                                    <div class="input-group-btn">
                                        <button type="button" class="btn btn-primary btn-plus">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary px-3">
                                    <i class="fa fa-shopping-cart mr-1"></i> Adicionar ao Carrinho
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="nav nav-tabs justify-content-center border-secondary mb-4">
                            <a class="nav-item nav-link active" data-toggle="tab" href="#tab-pane-1">Descrição</a>
                            <a class="nav-item nav-link" data-toggle="tab" href="#tab-pane-2">Informações</a>
                            <a class="nav-item nav-link" data-toggle="tab" href="#tab-pane-3">Avaliações (0)</a>
                        </div>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="tab-pane-1">
                                <h4 class="mb-3">Descrição do Produto</h4>
                                <p>Este é um produto da categoria <?php echo htmlspecialchars($product['categoria_nome']); ?>. 
                                   Um produto de alta qualidade com preço acessível.</p>
                            </div>
                            <div class="tab-pane fade" id="tab-pane-2">
                                <h4 class="mb-3">Informações Adicionais</h4>
                                <p>Informações detalhadas sobre o produto serão adicionadas aqui.</p>
                            </div>
                            <div class="tab-pane fade" id="tab-pane-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h4 class="mb-4">1 avaliação para "<?php echo htmlspecialchars($product['nome']); ?>"</h4>
                                        <div class="media mb-4">
                                            <div class="media-body">
                                                <h6>Nenhuma avaliação ainda</h6>
                                                <p>Seja o primeiro a avaliar este produto!</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h4 class="mb-4">Deixe uma avaliação</h4>
                                        <small>Você precisa estar logado para deixar uma avaliação.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Shop Detail End -->

    <!-- Products Start -->
    <div class="container-fluid py-5">
        <div class="text-center mb-4">
            <h2 class="section-title px-5"><span class="px-2">Produtos Relacionados</span></h2>
        </div>
        <div class="row px-xl-5">
            <?php
            if ($related_products && $related_products->num_rows > 0) {
                while($related = $related_products->fetch_assoc()) {
            ?>
            <div class="col-lg-3 col-md-6 col-sm-12 pb-1">
                <div class="card product-item border-0 mb-4">
                    <div class="card-header product-img position-relative overflow-hidden bg-transparent border p-0">
                        <img class="img-fluid w-100" src="img/<?php echo getProductImage($related['categoria_id']); ?>" alt="<?php echo htmlspecialchars($related['nome']); ?>">
                    </div>
                    <div class="card-body border-left border-right text-center p-0 pt-4 pb-3">
                        <h6 class="text-truncate mb-3"><?php echo htmlspecialchars($related['nome']); ?></h6>
                        <div class="d-flex justify-content-center">
                            <h6>R$ <?php echo number_format($related['preco'], 2, ',', '.'); ?></h6>
                        </div>
                        <small class="text-muted"><?php echo htmlspecialchars($related['categoria_nome']); ?></small>
                    </div>
                    <div class="card-footer d-flex justify-content-between bg-light border">
                        <a href="detail.php?id=<?php echo $related['id']; ?>" class="btn btn-sm text-dark p-0"><i class="fas fa-eye text-primary mr-1"></i>Ver Detalhes</a>
                        <a href="cart.php?action=add&id=<?php echo $related['id']; ?>" class="btn btn-sm text-dark p-0"><i class="fas fa-shopping-cart text-primary mr-1"></i>Adicionar ao Carrinho</a>
                    </div>
                </div>
            </div>
            <?php
                }
            } else {
                echo '<div class="col-12 text-center"><p>Nenhum produto relacionado encontrado.</p></div>';
            }
            ?>
        </div>
    </div>
    <!-- Products End -->

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
                            $categories->data_seek(0);
                            while($category = $categories->fetch_assoc()) {
                            ?>
                            <a class="text-dark mb-2" href="shop.php?categoria=<?php echo $category['id']; ?>">
                                <i class="fa fa-angle-right mr-2"></i><?php echo htmlspecialchars($category['nome']); ?>
                            </a>
                            <?php
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
    // Quantity buttons functionality
    $('.btn-plus, .btn-minus').on('click', function(e) {
        e.preventDefault();
        const $button = $(this);
        const $input = $button.closest('.quantity').find('input');
        const oldValue = parseInt($input.val());
        
        if ($button.hasClass('btn-plus')) {
            $input.val(oldValue + 1);
        } else if (oldValue > 1) {
            $input.val(oldValue - 1);
        }
    });
    </script>
</body>
</html> 