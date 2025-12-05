<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

try {
    $query = $pdo->query("
        SELECT a.*, c.nome as nome_cliente 
        FROM agendamentos a 
        LEFT JOIN clientes c ON a.cliente_id = c.id 
        WHERE a.data_agendamento = CURDATE() 
        ORDER BY a.hora_agendamento ASC
        LIMIT 5
    ");
    $agendamentos = $query->fetchAll(PDO::FETCH_ASSOC);
    
    $response = [
        'success' => true,
        'agendamentos' => $agendamentos
    ];
    
} catch (PDOException $e) {
    $response = [
        'success' => false,
        'message' => 'Erro ao carregar agendamentos: ' . $e->getMessage()
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
?>