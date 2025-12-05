<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = $_POST['cliente_id'] ?? '';
    $produto_id = $_POST['produto_id'] ?? '';
    $quantidade = $_POST['quantidade'] ?? 0;
    $valor_unitario = $_POST['valor_unitario'] ?? 0;
    $valor_total = $_POST['valor_total'] ?? 0;
    $observacao = $_POST['observacao'] ?? '';

    // Validações
    if (empty($cliente_id) || empty($produto_id) || $quantidade <= 0 || $valor_unitario <= 0) {
        echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios!']);
        exit;
    }

    try {
        // Verificar se há estoque suficiente
        $query_estoque = $pdo->prepare("SELECT quantidade_estoque, nome FROM produtos WHERE id = ?");
        $query_estoque->execute([$produto_id]);
        $produto = $query_estoque->fetch(PDO::FETCH_ASSOC);

        if (!$produto) {
            echo json_encode(['success' => false, 'message' => 'Produto não encontrado!']);
            exit;
        }

        if ($produto['quantidade_estoque'] < $quantidade) {
            echo json_encode(['success' => false, 'message' => 'Estoque insuficiente! Disponível: ' . $produto['quantidade_estoque']]);
            exit;
        }

        // Iniciar transação
        $pdo->beginTransaction();

        // Registrar a venda
        $query_venda = $pdo->prepare("INSERT INTO vendas (cliente_id, produto_id, quantidade, valor_unitario, valor_total, observacao) VALUES (?, ?, ?, ?, ?, ?)");
        $query_venda->execute([$cliente_id, $produto_id, $quantidade, $valor_unitario, $valor_total, $observacao]);

        // Atualizar o estoque do produto
        $query_estoque = $pdo->prepare("UPDATE produtos SET quantidade_estoque = quantidade_estoque - ? WHERE id = ?");
        $query_estoque->execute([$quantidade, $produto_id]);

        // Commit da transação
        $pdo->commit();

        echo json_encode(['success' => true, 'message' => 'Venda registrada com sucesso! Estoque atualizado.']);

    } catch (PDOException $e) {
        // Rollback em caso de erro
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erro ao registrar venda: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido!']);
}
?>