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

// Process delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];

    // Não permitir que o usuário exclua a si mesmo
    if ($user_id === $_SESSION['user_id']) {
        $_SESSION['error'] = "Você não pode excluir seu próprio usuário";
    } else {
        // Verificar se o usuário tem pedidos
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM pedidos WHERE usuario_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['total'] > 0) {
            $_SESSION['error'] = "Não é possível excluir um usuário que possui pedidos";
        } else {
            // Excluir o usuário
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $user_id);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Usuário excluído com sucesso!";
            } else {
                $_SESSION['error'] = "Erro ao excluir usuário";
            }
        }
    }

    header('Location: index.php');
    exit();
}

// Buscar todos os usuários com contagem de pedidos e valor total
$users = $conn->query("
    SELECT 
        u.*,
        COUNT(p.id) as total_pedidos,
        COALESCE(SUM(p.valor_total), 0) as valor_total_pedidos
    FROM usuarios u
    LEFT JOIN pedidos p ON u.id = p.usuario_id AND p.status != 'cancelado'
    GROUP BY u.id
    ORDER BY u.nome ASC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Usuários - EShopper Admin</title>
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

    <!-- Libraries Stylesheet -->
    <link href="../../lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="../../css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container-fluid pt-5">
        <div class="row px-xl-5">
            <div class="col-lg-12">
                <div class="card border-secondary mb-5">
                    <div class="card-header bg-secondary border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="font-weight-semi-bold m-0">Usuários</h4>
                            <a href="create.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Novo Usuário
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Telefone</th>
                                        <th>Admin</th>
                                        <th>Total Pedidos</th>
                                        <th>Valor Total</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['telefone']); ?></td>
                                        <td>
                                            <?php if ($user['is_admin']): ?>
                                                <span class="badge badge-success">Sim</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Não</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $user['total_pedidos']; ?></td>
                                        <td>R$ <?php echo number_format($user['valor_total_pedidos'], 2, ',', '.'); ?></td>
                                        <td>
                                            <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($user['id'] !== $_SESSION['user_id'] && $user['total_pedidos'] === 0): ?>
                                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal<?php echo $user['id']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <!-- Modal de Confirmação de Exclusão -->
                                    <?php if ($user['id'] !== $_SESSION['user_id'] && $user['total_pedidos'] === 0): ?>
                                    <div class="modal fade" id="deleteModal<?php echo $user['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?php echo $user['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel<?php echo $user['id']; ?>">Confirmar Exclusão</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    Tem certeza que deseja excluir o usuário <strong><?php echo htmlspecialchars($user['nome']); ?></strong>?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                    <a href="?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-danger">Excluir</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="../../lib/easing/easing.min.js"></script>
    <script src="../../lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="../../js/main.js"></script>
</body>
</html> 