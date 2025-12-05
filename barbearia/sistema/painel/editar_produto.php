<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $query = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $query->execute([$id]);
    $produto = $query->fetch(PDO::FETCH_ASSOC);
    
    if ($produto) {
        echo json_encode(['success' => true, 'produto' => $produto]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Produto não encontrado!']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $categoria = $_POST['categoria'] ?? '';
    $preco_custo = $_POST['preco_custo'] ?? 0;
    $preco_venda = $_POST['preco_venda'] ?? 0;
    $quantidade_minima = $_POST['quantidade_minima'] ?? 0;
    $codigo_barras = $_POST['codigo_barras'] ?? '';
    $observacao = $_POST['observacao'] ?? '';


    if (empty($nome) || empty($preco_venda)) {
        echo json_encode(['success' => false, 'message' => 'Nome e preço de venda são obrigatórios!']);
        exit;
    }

    try {
        $query = $pdo->prepare("UPDATE produtos SET nome = ?, descricao = ?, categoria = ?, preco_custo = ?, preco_venda = ?, quantidade_minima = ?, codigo_barras = ?, fornecedor = ?, status = ? WHERE id = ?");
        $query->execute([$nome, $descricao, $categoria, $preco_custo, $preco_venda, $quantidade_minima, $codigo_barras, $fornecedor, $status, $id]);

        echo json_encode(['success' => true, 'message' => 'Produto atualizado com sucesso!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar produto: ' . $e->getMessage()]);
    }
}
?>