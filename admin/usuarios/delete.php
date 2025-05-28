<?php
require_once '../../config.php';

session_start();

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID de usuário inválido";
    header('Location: index.php');
    exit();
}

$user_id = (int)$_GET['id'];

if ($user_id === $_SESSION['user_id']) {
    $_SESSION['error'] = "Você não pode excluir seu próprio usuário";
    header('Location: index.php');
    exit();
}

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM pedidos WHERE usuario_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
    $_SESSION['error'] = "Não é possível excluir um usuário que possui pedidos";
    header('Location: index.php');
    exit();
}

$stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Usuário excluído com sucesso!";
} else {
    $_SESSION['error'] = "Erro ao excluir usuário";
}

header('Location: index.php');
exit(); 