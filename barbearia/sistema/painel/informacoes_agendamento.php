<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

if (isset($_GET['id'])) {
    $id_agendamento = $_GET['id'];
    
    $query = $pdo->prepare("SELECT a.*, c.nome as nome_cliente, c.telefone, c.email FROM agendamentos a LEFT JOIN clientes c ON a.cliente_id = c.id WHERE a.id = ?");
    $query->execute([$id_agendamento]);
    $agendamento = $query->fetch(PDO::FETCH_ASSOC);
    
    if ($agendamento) {
        $response = [
            'success' => true,
            'agendamento' => $agendamento
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Agendamento não encontrado!'
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>