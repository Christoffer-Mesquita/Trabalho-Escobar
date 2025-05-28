<?php
require_once '../../config.php';

session_start();

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
                if (!empty($nome)) {
                    $sql = "INSERT INTO categorias (nome) VALUES (?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $nome);
                    $stmt->execute();
                }
                break;

            case 'edit':
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
                if ($id && !empty($nome)) {
                    $sql = "UPDATE categorias SET nome = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $nome, $id);
                    $stmt->execute();
                }
                break;

            case 'delete':
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                if ($id) {
                    $check_sql = "SELECT COUNT(*) as total FROM produtos WHERE categoria_id = ?";
                    $check_stmt = $conn->prepare($check_sql);
                    $check_stmt->bind_param("i", $id);
                    $check_stmt->execute();
                    $result = $check_stmt->get_result();
                    $count = $result->fetch_assoc()['total'];

                    if ($count == 0) {
                        $sql = "DELETE FROM categorias WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                    }
                }
                break;
        }
    }
}

$sql = "SELECT c.*, COUNT(p.id) as total_produtos 
        FROM categorias c 
        LEFT JOIN produtos p ON c.id = p.categoria_id 
        GROUP BY c.id 
        ORDER BY c.nome";
$categories = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <title>Gerenciar Categorias - EShopper Admin</title>
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
                            <a href="index.php" class="nav-item nav-link active">Categorias</a>
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

    <!-- Categories Start -->
    <div class="container-fluid pt-5">
        <div class="row px-xl-5">
            <div class="col-lg-12">
                <div class="card border-secondary">
                    <div class="card-header bg-secondary border-0">
                        <h4 class="font-weight-semi-bold m-0 text-white">Gerenciar Categorias</h4>
                    </div>
                    <div class="card-body">
                        <!-- Add Category Form -->
                        <form action="" method="POST" class="mb-4">
                            <input type="hidden" name="action" value="add">
                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nome da Categoria</label>
                                        <input type="text" class="form-control" name="nome" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-block">Adicionar Categoria</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Categories Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Total de Produtos</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($category = $categories->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $category['id']; ?></td>
                                        <td>
                                            <form action="" method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="edit">
                                                <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" name="nome" value="<?php echo htmlspecialchars($category['nome']); ?>" required>
                                                    <div class="input-group-append">
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fa fa-save"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </td>
                                        <td><?php echo $category['total_produtos']; ?></td>
                                        <td>
                                            <?php if ($category['total_produtos'] == 0): ?>
                                            <form action="" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta categoria?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                            <?php else: ?>
                                            <button class="btn btn-secondary btn-sm" disabled title="Não é possível excluir categorias com produtos">
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
    <!-- Categories End -->

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
</body>
</html> 