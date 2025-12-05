<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

try {
    $query = $pdo->query("
        SELECT * FROM clientes 
        ORDER BY data_cadastro DESC, id DESC 
        LIMIT 5
    ");
    $clientes = $query->fetchAll(PDO::FETCH_ASSOC);
    
    $response = [
        'success' => true,
        'clientes' => $clientes
    ];
    
} catch (PDOException $e) {
    $response = [
        'success' => false,
        'message' => 'Erro ao carregar clientes: ' . $e->getMessage()
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
?>