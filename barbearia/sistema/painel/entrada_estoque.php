<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produto_id = $_POST['produto_id'] ?? '';
    $quantidade = $_POST['quantidade'] ?? 0;
    $preco_custo = $_POST['preco_custo'] ?? 0;
    $observacao = $_POST['observacao'] ?? '';

    if (empty($produto_id) || $quantidade <= 0) {
        echo json_encode(['success' => false, 'message' => 'Produto e quantidade são obrigatórios!']);
        exit;
    }

    try {
        // Atualizar estoque
        $query = $pdo->prepare("UPDATE produtos SET quantidade_estoque = quantidade_estoque + ?, preco_custo = COALESCE(?, preco_custo) WHERE id = ?");
        $query->execute([$quantidade, $preco_custo, $produto_id]);

        // Registrar histórico (opcional - você pode criar uma tabela para histórico)
        // $query_historico = $pdo->prepare("INSERT INTO historico_estoque (produto_id, quantidade, tipo, observacao) VALUES (?, ?, 'entrada', ?)");
        // $query_historico->execute([$produto_id, $quantidade, $observacao]);

        echo json_encode(['success' => true, 'message' => 'Entrada no estoque registrada com sucesso!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao registrar entrada no estoque: ' . $e->getMessage()]);
    }
}
?>