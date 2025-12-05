<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $produto_id = $_POST['produto_id'];
    $tipo = $_POST['tipo'];
    $quantidade = $_POST['quantidade'];
    $observacao = $_POST['observacao'];
    
    try {
        // Registrar movimentação
        $query_mov = $pdo->prepare("INSERT INTO estoque_movimentacao SET produto_id = ?, tipo = ?, quantidade = ?, data_movimentacao = CURDATE(), observacao = ?");
        $query_mov->execute([$produto_id, $tipo, $quantidade, $observacao]);
        
        // Atualizar quantidade do produto
        if ($tipo == 'entrada') {
            $query_prod = $pdo->prepare("UPDATE produtos SET quantidade = quantidade + ? WHERE id = ?");
        } else {
            $query_prod = $pdo->prepare("UPDATE produtos SET quantidade = quantidade - ? WHERE id = ?");
        }
        $query_prod->execute([$quantidade, $produto_id]);
        
        $response = [
            'success' => true,
            'message' => 'Estoque movimentado com sucesso!'
        ];
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Erro ao movimentar estoque: ' . $e->getMessage()
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>