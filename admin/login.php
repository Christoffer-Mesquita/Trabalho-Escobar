<?php
// Include database configuration
require_once '../config.php';

// Initialize session
session_start();

// Check if user is already logged in
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
    header('Location: dashboard.php');
    exit();
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    $erro = null;

    // Validações
    if (empty($email)) {
        $erro = "Email é obrigatório";
    } elseif (empty($senha)) {
        $erro = "Senha é obrigatória";
    }

    if (!$erro) {
        // Verifica se o usuário existe e é admin
        $stmt = $conn->prepare("SELECT id, nome, senha FROM usuarios WHERE email = ? AND is_admin = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verifica a senha
            if (password_verify($senha, $user['senha'])) {
                // Login bem sucedido
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nome'];
                $_SESSION['is_admin'] = true;

                header('Location: dashboard.php');
                exit();
            } else {
                $erro = "Email ou senha inválidos";
            }
        } else {
            $erro = "Email ou senha inválidos";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <title>Login - EShopper Admin</title>
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
</head>

<body>
    <div class="container-fluid">
        <div class="row px-xl-5">
            <div class="col-lg-4 mx-auto">
                <div class="card border-secondary mb-5 mt-5">
                    <div class="card-header bg-secondary border-0">
                        <h4 class="font-weight-semi-bold m-0 text-center">Login Administrativo</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($erro)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $erro; ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST">
                            <div class="form-group">
                                <label>Email</label>
                                <input class="form-control" type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Senha</label>
                                <input class="form-control" type="password" name="senha" required>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block">Entrar</button>
                            </div>
                            <div class="text-center">
                                <a href="../index.php" class="text-secondary">
                                    <i class="fas fa-arrow-left mr-2"></i>Voltar para a Loja
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="../lib/easing/easing.min.js"></script>
    <script src="../lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="../js/main.js"></script>
</body>
</html> 