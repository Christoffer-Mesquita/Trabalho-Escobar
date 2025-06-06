<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.php');
    exit;
}

try {
    $conn->begin_transaction();

    $numero_cartao = preg_replace('/\D/', '', $_POST['numero_cartao']);
    $validade = $_POST['validade'];
    $cvv = $_POST['cvv'];
    $nome_cartao = $_POST['nome_cartao'];

    if (strlen($numero_cartao) < 13 || strlen($numero_cartao) > 19) {
        throw new Exception('Número do cartão inválido');
    }

    if (!preg_match('/^\d{2}\/\d{2}$/', $validade)) {
        throw new Exception('Data de validade inválida');
    }

    if (strlen($cvv) < 3 || strlen($cvv) > 4) {
        throw new Exception('CVV inválido');
    }

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
        $_POST['total'],
        $endereco_entrega,
        $_POST['observacoes']
    );
    
    $stmt->execute();
    $pedido_id = $conn->insert_id;

    foreach ($_SESSION['cart'] as $produto_id => $quantidade) {
        $stmt = $conn->prepare("SELECT * FROM produtos WHERE id = ? AND ativo = TRUE FOR UPDATE");
        $stmt->bind_param("i", $produto_id);
        $stmt->execute();
        $produto = $stmt->get_result()->fetch_assoc();

        if (!$produto) {
            throw new Exception('Produto não encontrado ou inativo');
        }

        if ($produto['estoque'] < $quantidade) {
            throw new Exception("Estoque insuficiente para o produto: {$produto['nome']}");
        }

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
            $produto_id,
            $quantidade,
            $produto['preco']
        );
        
        $stmt->execute();

        $stmt = $conn->prepare("
            UPDATE produtos 
            SET estoque = estoque - ? 
            WHERE id = ?
        ");

        $stmt->bind_param("ii", $quantidade, $produto_id);
        $stmt->execute();
    }

    $conn->commit();

    unset($_SESSION['cart']);

    $_SESSION['pedido_sucesso'] = $pedido_id;
    header('Location: pedido_sucesso.php');
    exit;

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['erro_checkout'] = $e->getMessage();
    header('Location: checkout.php');
    exit;
} 