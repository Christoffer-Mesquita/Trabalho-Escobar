<?php
// Include database configuration
require_once 'config.php';

// Initialize cart session if not exists
session_start();
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

// Get cart products
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

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $erro = null;
    
    // Validações básicas do cartão
    $numero_cartao = preg_replace('/\D/', '', $_POST['numero_cartao']);
    $validade = $_POST['validade'];
    $cvv = $_POST['cvv'];
    $nome_cartao = trim($_POST['nome_cartao']);
    
    if (strlen($numero_cartao) < 13 || strlen($numero_cartao) > 19) {
        $erro = "Número do cartão inválido";
    } elseif (!preg_match('/^\d{2}\/\d{2}$/', $validade)) {
        $erro = "Data de validade inválida";
    } elseif (strlen($cvv) < 3 || strlen($cvv) > 4) {
        $erro = "CVV inválido";
    } elseif (empty($nome_cartao)) {
        $erro = "Nome no cartão é obrigatório";
    }
    
    if (!$erro) {
        // Simula processamento do pagamento
        // Em um sistema real, aqui seria feita a integração com a operadora de cartão
        
        // Monta o endereço de entrega
        $endereco_entrega = sprintf(
            "%s, %s%s - %s, %s/%s - CEP: %s",
            $_POST['endereco'],
            $_POST['numero'],
            !empty($_POST['complemento']) ? ', ' . $_POST['complemento'] : '',
            $_POST['bairro'],
            $_POST['cidade'],
            $_POST['estado'],
            $_POST['cep']
        );
        
        try {
            // Inicia a transação
            $conn->begin_transaction();
            
            // Cria o pedido
            $stmt = $conn->prepare("
                INSERT INTO pedidos (
                    usuario_id, 
                    total, 
                    endereco_entrega, 
                    observacoes, 
                    status
                ) VALUES (?, ?, ?, ?, 'aprovado')
            ");
            
            $stmt->bind_param("idss", 
                $_SESSION['user_id'],
                $total,
                $endereco_entrega,
                $_POST['observacoes']
            );
            
            $stmt->execute();
            $pedido_id = $conn->insert_id;
            
            // Insere os itens do pedido e atualiza o estoque
            foreach ($cart_products as $item) {
                // Verifica estoque
                $stmt = $conn->prepare("SELECT estoque FROM produtos WHERE id = ? FOR UPDATE");
                $stmt->bind_param("i", $item['id']);
                $stmt->execute();
                $estoque_atual = $stmt->get_result()->fetch_assoc()['estoque'];
                
                if ($estoque_atual < $item['quantidade']) {
                    throw new Exception("Estoque insuficiente para o produto: {$item['nome']}");
                }
                
                // Insere item do pedido
                $stmt = $conn->prepare("
                    INSERT INTO pedidos_itens (
                        pedido_id, 
                        produto_id, 
                        quantidade, 
                        preco_unitario
                    ) VALUES (?, ?, ?, ?)
                ");
                
                $stmt->bind_param("iiid", 
                    $pedido_id,
                    $item['id'],
                    $item['quantidade'],
                    $item['preco']
                );
                
                $stmt->execute();
                
                // Atualiza estoque
                $stmt = $conn->prepare("
                    UPDATE produtos 
                    SET estoque = estoque - ? 
                    WHERE id = ?
                ");
                
                $stmt->bind_param("ii", $item['quantidade'], $item['id']);
                $stmt->execute();
            }
            
            // Confirma a transação
            $conn->commit();
            
            // Limpa o carrinho
            $_SESSION['cart'] = [];
            
            // Redireciona para página de sucesso
            $_SESSION['pedido_sucesso'] = $pedido_id;
            header('Location: pedido_sucesso.php');
            exit;
            
        } catch (Exception $e) {
            // Em caso de erro, desfaz a transação
            $conn->rollback();
            $erro = $e->getMessage();
        }
    }
}

// Function to get product image based on category
function getProductImage($categoria_id) {
    $images = [
        1 => 'product-1.jpg', // Roupas
        2 => 'product-2.jpg', // Calçados
        3 => 'product-3.jpg'  // Acessórios
    ];
    return isset($images[$categoria_id]) ? $images[$categoria_id] : 'product-1.jpg';
}

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Verifica se há itens no carrinho
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Busca informações do usuário
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

// Calcula o total do pedido
$total = 0;
$itens = [];

foreach ($_SESSION['cart'] as $produto_id => $quantidade) {
    $stmt = $conn->prepare("SELECT p.*, c.nome as categoria_nome 
                          FROM produtos p 
                          JOIN categorias c ON p.categoria_id = c.id 
                          WHERE p.id = ? AND p.ativo = TRUE");
    $stmt->bind_param("i", $produto_id);
    $stmt->execute();
    $produto = $stmt->get_result()->fetch_assoc();
    
    if ($produto) {
        $subtotal = $produto['preco'] * $quantidade;
        $total += $subtotal;
        $itens[] = [
            'id' => $produto['id'],
            'nome' => $produto['nome'],
            'preco' => $produto['preco'],
            'quantidade' => $quantidade,
            'subtotal' => $subtotal,
            'categoria_nome' => $produto['categoria_nome'],
            'categoria_id' => $produto['categoria_id']
        ];
    }
}

// Exibe mensagem de erro se houver
$erro_checkout = isset($_SESSION['erro_checkout']) ? $_SESSION['erro_checkout'] : '';
unset($_SESSION['erro_checkout']);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <title>Checkout - EShopper</title>
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
                            <a href="cart.php" class="nav-item nav-link">Carrinho</a>
                            <a href="checkout.php" class="nav-item nav-link active">Checkout</a>
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

    <!-- Checkout Start -->
    <div class="container-fluid pt-5">
        <?php if ($erro_checkout): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($erro_checkout); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="checkout.php" method="POST" id="checkout-form">
            <div class="row px-xl-5">
                <div class="col-lg-8">
                    <div class="mb-4">
                        <h4 class="font-weight-semi-bold mb-4">Endereço de Entrega</h4>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Nome</label>
                                <input class="form-control" type="text" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>E-mail</label>
                                <input class="form-control" type="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Telefone</label>
                                <input class="form-control" type="text" name="telefone" value="<?php echo htmlspecialchars($usuario['telefone']); ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>CEP</label>
                                <input class="form-control" type="text" name="cep" value="<?php echo htmlspecialchars($usuario['cep']); ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Endereço</label>
                                <input class="form-control" type="text" name="endereco" value="<?php echo htmlspecialchars($usuario['endereco']); ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Número</label>
                                <input class="form-control" type="text" name="numero" value="<?php echo htmlspecialchars($usuario['numero']); ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Complemento</label>
                                <input class="form-control" type="text" name="complemento" value="<?php echo htmlspecialchars($usuario['complemento']); ?>">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Bairro</label>
                                <input class="form-control" type="text" name="bairro" value="<?php echo htmlspecialchars($usuario['bairro']); ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Cidade</label>
                                <input class="form-control" type="text" name="cidade" value="<?php echo htmlspecialchars($usuario['cidade']); ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Estado</label>
                                <select class="form-control" name="estado" required>
                                    <option value="">Selecione...</option>
                                    <?php
                                    $estados = [
                                        'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 'AM' => 'Amazonas',
                                        'BA' => 'Bahia', 'CE' => 'Ceará', 'DF' => 'Distrito Federal', 'ES' => 'Espírito Santo',
                                        'GO' => 'Goiás', 'MA' => 'Maranhão', 'MT' => 'Mato Grosso', 'MS' => 'Mato Grosso do Sul',
                                        'MG' => 'Minas Gerais', 'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná',
                                        'PE' => 'Pernambuco', 'PI' => 'Piauí', 'RJ' => 'Rio de Janeiro', 'RN' => 'Rio Grande do Norte',
                                        'RS' => 'Rio Grande do Sul', 'RO' => 'Rondônia', 'RR' => 'Roraima', 'SC' => 'Santa Catarina',
                                        'SP' => 'São Paulo', 'SE' => 'Sergipe', 'TO' => 'Tocantins'
                                    ];
                                    foreach ($estados as $sigla => $nome) {
                                        $selected = ($usuario['estado'] === $sigla) ? 'selected' : '';
                                        echo "<option value=\"$sigla\" $selected>$nome</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h4 class="font-weight-semi-bold mb-4">Dados do Cartão</h4>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Número do Cartão</label>
                                <input class="form-control" type="text" name="numero_cartao" placeholder="0000 0000 0000 0000" required>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Validade</label>
                                <input class="form-control" type="text" name="validade" placeholder="MM/AA" required>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>CVV</label>
                                <input class="form-control" type="text" name="cvv" placeholder="123" required>
                            </div>
                            <div class="col-md-12 form-group">
                                <label>Nome no Cartão</label>
                                <input class="form-control" type="text" name="nome_cartao" placeholder="NOME COMO ESTÁ NO CARTÃO" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h4 class="font-weight-semi-bold mb-4">Observações</h4>
                        <div class="form-group">
                            <textarea class="form-control" name="observacoes" rows="3" placeholder="Observações sobre o pedido (opcional)"></textarea>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-secondary mb-5">
                        <div class="card-header bg-secondary border-0">
                            <h4 class="font-weight-semi-bold m-0">Resumo do Pedido</h4>
                        </div>
                        <div class="card-body">
                            <?php foreach ($itens as $item): ?>
                                <div class="d-flex justify-content-between mb-3">
                                    <div class="d-flex align-items-center">
                                        <img src="img/<?php echo getProductImage($item['categoria_id']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['nome']); ?>" 
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                        <div class="ml-3">
                                            <h6 class="text-truncate mb-0"><?php echo htmlspecialchars($item['nome']); ?></h6>
                                            <small class="text-muted">Qtd: <?php echo $item['quantidade']; ?></small>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <h6 class="font-weight-medium">R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></h6>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <hr>
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
                            <button type="submit" class="btn btn-block btn-primary my-3 py-3">Finalizar Pedido</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- Checkout End -->

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
    // Máscara para número do cartão
    document.querySelector('input[name="numero_cartao"]').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/(\d{4})/g, '$1 ').trim();
        e.target.value = value;
    });

    // Máscara para validade
    document.querySelector('input[name="validade"]').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.substring(0,2) + '/' + value.substring(2,4);
        }
        e.target.value = value;
    });

    // Máscara para CVV
    document.querySelector('input[name="cvv"]').addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '').substring(0,3);
    });

    // Máscara para telefone
    document.querySelector('input[name="telefone"]').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 11) value = value.substring(0,11);
        value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
        value = value.replace(/(\d)(\d{4})$/, '$1-$2');
        e.target.value = value;
    });

    // Máscara para CEP
    document.querySelector('input[name="cep"]').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 5) {
            value = value.substring(0,5) + '-' + value.substring(5,8);
        }
        e.target.value = value;
    });
    </script>
</body>
</html> 