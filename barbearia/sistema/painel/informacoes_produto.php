<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $query = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $query->execute([$id]);
    $produto = $query->fetch(PDO::FETCH_ASSOC);
    
    if ($produto) {
        echo json_encode(['success' => true, 'produto' => $produto]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Produto não encontrado!']);
    }
}
?>