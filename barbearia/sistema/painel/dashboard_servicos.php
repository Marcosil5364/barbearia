<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

try {
    $query = $pdo->query("
        SELECT servico, COUNT(*) as quantidade 
        FROM agendamentos 
        WHERE MONTH(data_agendamento) = MONTH(CURDATE()) 
        AND YEAR(data_agendamento) = YEAR(CURDATE())
        GROUP BY servico 
        ORDER BY quantidade DESC
        LIMIT 10
    ");
    $servicos = $query->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular total
    $total = 0;
    foreach ($servicos as $servico) {
        $total += $servico['quantidade'];
    }
    
    $response = [
        'success' => true,
        'servicos' => $servicos,
        'total' => $total
    ];
    
} catch (PDOException $e) {
    $response = [
        'success' => false,
        'message' => 'Erro ao carregar serviços: ' . $e->getMessage()
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
?>