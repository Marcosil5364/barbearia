<?php
session_start();
require_once("../conexao.php");

// Verificar se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

try {
    // Buscar agendamentos com informações do cliente
    $query = $pdo->query("
        SELECT a.*, c.nome as nome_cliente 
        FROM agendamentos a 
        LEFT JOIN clientes c ON a.cliente_id = c.id 
        ORDER BY a.data_agendamento DESC, a.hora_agendamento DESC
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