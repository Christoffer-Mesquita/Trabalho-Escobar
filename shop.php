<?php
require_once 'config.php';
session_start();

// Inicializa o carrinho se não existir
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Parâmetros de paginação e filtros
$categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$itens_por_pagina = 12;
$offset = ($pagina - 1) * $itens_por_pagina;

// Busca todas as categorias para o filtro
$categorias = $conn->query("SELECT * FROM categorias ORDER BY nome")->fetch_all(MYSQLI_ASSOC);

// Prepara a query base
$sql = "SELECT p.*, c.nome as categoria_nome 
        FROM produtos p 
        JOIN categorias c ON p.categoria_id = c.id 
        WHERE p.ativo = TRUE";
$params = [];
$types = "";

// Adiciona filtro de categoria se especificado
if ($categoria_id > 0) {
    $sql .= " AND p.categoria_id = ?";
    $params[] = $categoria_id;
    $types .= "i";
}

// Conta o total de produtos para paginação
$count_sql = str_replace("p.*, c.nome as categoria_nome", "COUNT(*) as total", $sql);
$stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_produtos = $stmt->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_produtos / $itens_por_pagina);

// Adiciona ordenação e limite para paginação
$sql .= " ORDER BY p.data_cadastro DESC LIMIT ? OFFSET ?";
$params[] = $itens_por_pagina;
$params[] = $offset;
$types .= "ii";

// Busca os produtos
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$produtos = [];
while ($row = $result->fetch_assoc()) {
    $produtos[] = $row;
}

// Function to get product image
function getProductImage($produto_id, $categoria_id) {
    $images = [
        1 => 'product-1.jpg', // Roupas
        2 => 'product-2.jpg', // Calçados
        3 => 'product-3.jpg'  // Acessórios
    ];
    $default_image = isset($images[$categoria_id]) ? $images[$categoria_id] : 'product-1.jpg';
    return file_exists("img/produto-{$produto_id}.jpg") ? "produto-{$produto_id}.jpg" : $default_image;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loja - EShopper</title>
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
                    <a class="breadcrumb-item text-dark" href="shop.php">Loja</a>
                    <?php if ($categoria_id > 0): 
                        $categoria_atual = array_filter($categorias, function($cat) use ($categoria_id) {
                            return $cat['id'] == $categoria_id;
                        });
                        $categoria_atual = reset($categoria_atual);
                    ?>
                        <span class="breadcrumb-item active"><?php echo htmlspecialchars($categoria_atual['nome']); ?></span>
                    <?php else: ?>
                        <span class="breadcrumb-item active">Todos os Produtos</span>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->

    <!-- Shop Start -->
    <div class="container-fluid">
        <div class="row px-xl-5">
            <!-- Shop Sidebar Start -->
            <div class="col-lg-3 col-md-4">
                <!-- Categorias Start -->
                <h5 class="section-title position-relative text-uppercase mb-3">
                    <span class="bg-secondary pr-3">Categorias</span>
                </h5>
                <div class="bg-light p-4 mb-30">
                    <div class="list-group">
                        <a href="shop.php" class="list-group-item list-group-item-action <?php echo $categoria_id == 0 ? 'active' : ''; ?>">
                            Todas as Categorias
                        </a>
                        <?php foreach ($categorias as $categoria): ?>
                            <a href="shop.php?categoria=<?php echo $categoria['id']; ?>" 
                               class="list-group-item list-group-item-action <?php echo $categoria_id == $categoria['id'] ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($categoria['nome']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!-- Categorias End -->
            </div>
            <!-- Shop Sidebar End -->

            <!-- Shop Product Start -->
            <div class="col-lg-9 col-md-8">
                <div class="row pb-3">
                    <div class="col-12 pb-1">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div>
                                <h5 class="section-title position-relative text-uppercase mb-3">
                                    <span class="bg-secondary pr-3">
                                        <?php echo $categoria_id > 0 ? htmlspecialchars($categoria_atual['nome']) : 'Todos os Produtos'; ?>
                                    </span>
                                </h5>
                                <p class="text-muted">
                                    <?php echo $total_produtos; ?> produto<?php echo $total_produtos != 1 ? 's' : ''; ?> encontrado<?php echo $total_produtos != 1 ? 's' : ''; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($produtos)): ?>
                        <div class="col-12 text-center py-5">
                            <h3>Nenhum produto encontrado</h3>
                            <p>Tente selecionar outra categoria ou volte mais tarde.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($produtos as $produto): ?>
                            <div class="col-lg-4 col-md-6 col-sm-12 pb-1">
                                <div class="card product-item border-0 mb-4">
                                    <div class="card-header product-img position-relative overflow-hidden bg-transparent border p-0">
                                        <img class="img-fluid w-100" 
                                             src="img/<?php echo getProductImage($produto['id'], $produto['categoria_id']); ?>" 
                                             alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                                    </div>
                                    <div class="card-body border-left border-right text-center p-0 pt-4 pb-3">
                                        <h6 class="text-truncate mb-3"><?php echo htmlspecialchars($produto['nome']); ?></h6>
                                        <div class="d-flex justify-content-center">
                                            <h6>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></h6>
                                        </div>
                                        <small class="text-muted"><?php echo htmlspecialchars($produto['categoria_nome']); ?></small>
                                    </div>
                                    <div class="card-footer d-flex justify-content-between bg-light border">
                                        <a href="detail.php?id=<?php echo $produto['id']; ?>" class="btn btn-sm text-dark p-0">
                                            <i class="fas fa-eye text-primary mr-1"></i>Ver Detalhes
                                        </a>
                                        <a href="cart.php?action=add&id=<?php echo $produto['id']; ?>" class="btn btn-sm text-dark p-0">
                                            <i class="fas fa-shopping-cart text-primary mr-1"></i>Adicionar ao Carrinho
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Paginação -->
                        <?php if ($total_paginas > 1): ?>
                            <div class="col-12">
                                <nav>
                                    <ul class="pagination justify-content-center">
                                        <?php if ($pagina > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?categoria=<?php echo $categoria_id; ?>&pagina=<?php echo $pagina - 1; ?>">
                                                    Anterior
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                            <li class="page-item <?php echo $i == $pagina ? 'active' : ''; ?>">
                                                <a class="page-link" href="?categoria=<?php echo $categoria_id; ?>&pagina=<?php echo $i; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($pagina < $total_paginas): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?categoria=<?php echo $categoria_id; ?>&pagina=<?php echo $pagina + 1; ?>">
                                                    Próxima
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Shop Product End -->
        </div>
    </div>
    <!-- Shop End -->

    <?php include 'includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 