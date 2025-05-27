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

// Process product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
                $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_STRING);
                $preco = filter_input(INPUT_POST, 'preco', FILTER_VALIDATE_FLOAT);
                $estoque = filter_input(INPUT_POST, 'estoque', FILTER_VALIDATE_INT);
                $categoria_id = filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT);

                if (!empty($nome) && $preco !== false && $estoque !== false && $categoria_id) {
                    $sql = "INSERT INTO produtos (nome, descricao, preco, estoque, categoria_id) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssdii", $nome, $descricao, $preco, $estoque, $categoria_id);
                    $stmt->execute();

                    // Handle image upload
                    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                        $produto_id = $conn->insert_id;
                        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
                        $filename = "produto-{$produto_id}.{$ext}";
                        $upload_path = "../../img/{$filename}";
                        
                        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $upload_path)) {
                            $sql = "UPDATE produtos SET imagem = ? WHERE id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("si", $filename, $produto_id);
                            $stmt->execute();
                        }
                    }
                }
                break;

            case 'edit':
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
                $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_STRING);
                $preco = filter_input(INPUT_POST, 'preco', FILTER_VALIDATE_FLOAT);
                $estoque = filter_input(INPUT_POST, 'estoque', FILTER_VALIDATE_INT);
                $categoria_id = filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT);

                if ($id && !empty($nome) && $preco !== false && $estoque !== false && $categoria_id) {
                    $sql = "UPDATE produtos SET nome = ?, descricao = ?, preco = ?, estoque = ?, categoria_id = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssdiii", $nome, $descricao, $preco, $estoque, $categoria_id, $id);
                    $stmt->execute();

                    // Handle image upload
                    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
                        $filename = "produto-{$id}.{$ext}";
                        $upload_path = "../../img/{$filename}";
                        
                        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $upload_path)) {
                            $sql = "UPDATE produtos SET imagem = ? WHERE id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("si", $filename, $id);
                            $stmt->execute();
                        }
                    }
                }
                break;

            case 'delete':
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                if ($id) {
                    // Check if product has orders
                    $check_sql = "SELECT COUNT(*) as total FROM pedidos_itens WHERE produto_id = ?";
                    $check_stmt = $conn->prepare($check_sql);
                    $check_stmt->bind_param("i", $id);
                    $check_stmt->execute();
                    $result = $check_stmt->get_result();
                    $count = $result->fetch_assoc()['total'];

                    if ($count == 0) {
                        // Delete product image if exists
                        $sql = "SELECT imagem FROM produtos WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($product = $result->fetch_assoc()) {
                            if (!empty($product['imagem'])) {
                                $image_path = "../../img/" . $product['imagem'];
                                if (file_exists($image_path)) {
                                    unlink($image_path);
                                }
                            }
                        }

                        // Delete product
                        $sql = "DELETE FROM produtos WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                    }
                }
                break;
        }
    }
}

// Get all products with category names
$sql = "SELECT p.*, c.nome as categoria_nome, 
        (SELECT COUNT(*) FROM pedidos_itens WHERE produto_id = p.id) as total_pedidos 
        FROM produtos p 
        JOIN categorias c ON p.categoria_id = c.id 
        ORDER BY p.nome";
$products = $conn->query($sql);

// Get all categories for the form
$sql = "SELECT * FROM categorias ORDER BY nome";
$categories = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <title>Gerenciar Produtos - EShopper Admin</title>
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
                            <a href="index.php" class="nav-item nav-link active">Produtos</a>
                            <a href="../categorias/index.php" class="nav-item nav-link">Categorias</a>
                            <a href="../vendas/index.php" class="nav-item nav-link">Vendas</a>
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

    <!-- Products Start -->
    <div class="container-fluid pt-5">
        <div class="row px-xl-5">
            <div class="col-lg-12">
                <div class="card border-secondary">
                    <div class="card-header bg-secondary border-0">
                        <h4 class="font-weight-semi-bold m-0 text-white">Gerenciar Produtos</h4>
                    </div>
                    <div class="card-body">
                        <!-- Add Product Form -->
                        <form action="" method="POST" enctype="multipart/form-data" class="mb-4">
                            <input type="hidden" name="action" value="add">
                            <div class="form-row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Nome do Produto</label>
                                        <input type="text" class="form-control" name="nome" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Categoria</label>
                                        <select class="form-control" name="categoria_id" required>
                                            <option value="">Selecione uma categoria</option>
                                            <?php while($category = $categories->fetch_assoc()): ?>
                                            <option value="<?php echo $category['id']; ?>">
                                                <?php echo htmlspecialchars($category['nome']); ?>
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Imagem</label>
                                        <input type="file" class="form-control-file" name="imagem" accept="image/*">
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Descrição</label>
                                        <textarea class="form-control" name="descricao" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Preço</label>
                                        <input type="number" class="form-control" name="preco" step="0.01" min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Estoque</label>
                                        <input type="number" class="form-control" name="estoque" min="0" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Adicionar Produto</button>
                            </div>
                        </form>

                        <!-- Products Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Imagem</th>
                                        <th>Nome</th>
                                        <th>Categoria</th>
                                        <th>Preço</th>
                                        <th>Estoque</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($product = $products->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $product['id']; ?></td>
                                        <td>
                                            <?php if (!empty($product['imagem'])): ?>
                                            <img src="../../img/<?php echo htmlspecialchars($product['imagem']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['nome']); ?>" 
                                                 style="max-width: 50px;">
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form action="" method="POST" enctype="multipart/form-data">
                                                <input type="hidden" name="action" value="edit">
                                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                                <div class="form-group mb-2">
                                                    <input type="text" class="form-control" name="nome" 
                                                           value="<?php echo htmlspecialchars($product['nome']); ?>" required>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <textarea class="form-control" name="descricao" rows="2"><?php echo htmlspecialchars($product['descricao']); ?></textarea>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <select class="form-control" name="categoria_id" required>
                                                        <?php 
                                                        $categories->data_seek(0);
                                                        while($category = $categories->fetch_assoc()): 
                                                        ?>
                                                        <option value="<?php echo $category['id']; ?>" 
                                                                <?php echo $category['id'] == $product['categoria_id'] ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($category['nome']); ?>
                                                        </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <input type="number" class="form-control" name="preco" 
                                                           value="<?php echo $product['preco']; ?>" step="0.01" min="0" required>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <input type="number" class="form-control" name="estoque" 
                                                           value="<?php echo $product['estoque']; ?>" min="0" required>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <input type="file" class="form-control-file" name="imagem" accept="image/*">
                                                </div>
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="fa fa-save"></i> Salvar
                                                </button>
                                            </form>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['categoria_nome']); ?></td>
                                        <td>R$ <?php echo number_format($product['preco'], 2, ',', '.'); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $product['estoque'] <= 5 ? 'danger' : 'success'; ?>">
                                                <?php echo $product['estoque']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($product['total_pedidos'] == 0): ?>
                                            <form action="" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este produto?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                            <?php else: ?>
                                            <button class="btn btn-secondary btn-sm" disabled title="Não é possível excluir produtos com pedidos">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
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
    <!-- Products End -->

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
</body>
</html> 