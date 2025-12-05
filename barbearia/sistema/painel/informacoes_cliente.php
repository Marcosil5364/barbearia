<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

if (isset($_GET['id'])) {
    $id_cliente = $_GET['id'];
    
    // Buscar dados do cliente
    $query = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
    $query->execute([$id_cliente]);
    $cliente = $query->fetch(PDO::FETCH_ASSOC);
    
    if ($cliente) {
        // Buscar agendamentos do cliente
        $query_agendamentos = $pdo->prepare("SELECT * FROM agendamentos WHERE cliente_id = ? ORDER BY data_agendamento DESC");
        $query_agendamentos->execute([$id_cliente]);
        $agendamentos = $query_agendamentos->fetchAll(PDO::FETCH_ASSOC);
        
        $response = [
            'success' => true,
            'cliente' => $cliente,
            'agendamentos' => $agendamentos
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Cliente não encontrado!'
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>