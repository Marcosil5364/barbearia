<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        $query = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
        $query->execute([$id]);
        
        echo json_encode(['success' => true, 'message' => 'Produto excluído com sucesso!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir produto: ' . $e->getMessage()]);
    }
}
?>