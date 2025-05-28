<?php
require_once '../../config.php';

session_start();

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../login.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $telefone = trim($_POST['telefone'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $complemento = trim($_POST['complemento'] ?? '');
    $bairro = trim($_POST['bairro'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $cep = trim($_POST['cep'] ?? '');
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    if (empty($nome)) {
        $errors[] = "O nome é obrigatório";
    }
    if (empty($email)) {
        $errors[] = "O email é obrigatório";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido";
    }
    if (empty($senha)) {
        $errors[] = "A senha é obrigatória";
    } elseif (strlen($senha) < 6) {
        $errors[] = "A senha deve ter pelo menos 6 caracteres";
    }
    if (empty($telefone)) {
        $errors[] = "O telefone é obrigatório";
    }
    if (empty($endereco)) {
        $errors[] = "O endereço é obrigatório";
    }
    if (empty($numero)) {
        $errors[] = "O número é obrigatório";
    }
    if (empty($bairro)) {
        $errors[] = "O bairro é obrigatório";
    }
    if (empty($cidade)) {
        $errors[] = "A cidade é obrigatória";
    }
    if (empty($estado)) {
        $errors[] = "O estado é obrigatório";
    }
    if (empty($cep)) {
        $errors[] = "O CEP é obrigatório";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = "Este email já está em uso";
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO usuarios (
                nome, email, senha, telefone, endereco, numero, complemento,
                bairro, cidade, estado, cep, is_admin
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt->bind_param(
            "sssssssssssi",
            $nome, $email, $senha_hash, $telefone, $endereco, $numero,
            $complemento, $bairro, $cidade, $estado, $cep, $is_admin
        );

        if ($stmt->execute()) {
            $_SESSION['success'] = "Usuário criado com sucesso!";
            header('Location: index.php');
            exit();
        } else {
            $errors[] = "Erro ao criar usuário";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Novo Usuário - EShopper Admin</title>
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
                        <h4 class="font-weight-semi-bold m-0">Novo Usuário</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Nome *</label>
                                    <input class="form-control" type="text" name="nome" value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Email *</label>
                                    <input class="form-control" type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Senha *</label>
                                    <input class="form-control" type="password" name="senha" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Telefone *</label>
                                    <input class="form-control" type="text" name="telefone" value="<?php echo htmlspecialchars($_POST['telefone'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Endereço *</label>
                                    <input class="form-control" type="text" name="endereco" value="<?php echo htmlspecialchars($_POST['endereco'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-2 form-group">
                                    <label>Número *</label>
                                    <input class="form-control" type="text" name="numero" value="<?php echo htmlspecialchars($_POST['numero'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>Complemento</label>
                                    <input class="form-control" type="text" name="complemento" value="<?php echo htmlspecialchars($_POST['complemento'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>Bairro *</label>
                                    <input class="form-control" type="text" name="bairro" value="<?php echo htmlspecialchars($_POST['bairro'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>Cidade *</label>
                                    <input class="form-control" type="text" name="cidade" value="<?php echo htmlspecialchars($_POST['cidade'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-2 form-group">
                                    <label>Estado *</label>
                                    <input class="form-control" type="text" name="estado" value="<?php echo htmlspecialchars($_POST['estado'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-2 form-group">
                                    <label>CEP *</label>
                                    <input class="form-control" type="text" name="cep" value="<?php echo htmlspecialchars($_POST['cep'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-12 form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_admin" name="is_admin" <?php echo isset($_POST['is_admin']) ? 'checked' : ''; ?>>
                                        <label class="custom-control-label" for="is_admin">É administrador</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary">Criar Usuário</button>
                                <a href="index.php" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
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

    <!-- Input Mask -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(document).ready(function(){
            $('input[name="telefone"]').mask('(00) 00000-0000');
            $('input[name="cep"]').mask('00000-000');
        });
    </script>
</body>
</html> 