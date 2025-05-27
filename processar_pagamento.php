<?php
require_once 'config.php';
session_start();

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

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.php');
    exit;
}

try {
    // Inicia a transação
    $conn->begin_transaction();

    // Simula processamento do pagamento (aceita qualquer cartão)
    $numero_cartao = preg_replace('/\D/', '', $_POST['numero_cartao']);
    $validade = $_POST['validade'];
    $cvv = $_POST['cvv'];
    $nome_cartao = $_POST['nome_cartao'];

    // Validações básicas
    if (strlen($numero_cartao) < 13 || strlen($numero_cartao) > 19) {
        throw new Exception('Número do cartão inválido');
    }

    if (!preg_match('/^\d{2}\/\d{2}$/', $validade)) {
        throw new Exception('Data de validade inválida');
    }

    if (strlen($cvv) < 3 || strlen($cvv) > 4) {
        throw new Exception('CVV inválido');
    }

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
        $_POST['total'],
        $endereco_entrega,
        $_POST['observacoes']
    );
    
    $stmt->execute();
    $pedido_id = $conn->insert_id;

    // Insere os itens do pedido e atualiza o estoque
    foreach ($_SESSION['cart'] as $produto_id => $quantidade) {
        // Busca informações do produto
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

        // Insere o item no pedido
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

        // Atualiza o estoque
        $stmt = $conn->prepare("
            UPDATE produtos 
            SET estoque = estoque - ? 
            WHERE id = ?
        ");

        $stmt->bind_param("ii", $quantidade, $produto_id);
        $stmt->execute();
    }

    // Confirma a transação
    $conn->commit();

    // Limpa o carrinho
    unset($_SESSION['cart']);

    // Redireciona para página de sucesso
    $_SESSION['pedido_sucesso'] = $pedido_id;
    header('Location: pedido_sucesso.php');
    exit;

} catch (Exception $e) {
    // Em caso de erro, desfaz a transação
    $conn->rollback();
    $_SESSION['erro_checkout'] = $e->getMessage();
    header('Location: checkout.php');
    exit;
} 