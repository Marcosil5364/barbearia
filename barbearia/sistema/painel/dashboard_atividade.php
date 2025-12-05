<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

try {
    // Buscar atividades reais do banco
    $atividades = [];
    
    // Últimos agendamentos
    $query_agendamentos = $pdo->query("
        SELECT a.*, c.nome as cliente_nome 
        FROM agendamentos a 
        LEFT JOIN clientes c ON a.cliente_id = c.id 
        ORDER BY a.data_agendamento DESC, a.hora_agendamento DESC 
        LIMIT 5
    ");
    $ultimos_agendamentos = $query_agendamentos->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($ultimos_agendamentos as $agendamento) {
        $atividades[] = [
            'icone' => 'bi-calendar-check',
            'cor' => 'primary',
            'titulo' => 'Agendamento Realizado',
            'descricao' => $agendamento['servico'] . ' para ' . $agendamento['cliente_nome'],
            'tempo' => 'Hoje às ' . $agendamento['hora_agendamento']
        ];
    }
    
    // Últimos clientes
    $query_clientes = $pdo->query("
        SELECT * FROM clientes 
        ORDER BY data_cadastro DESC 
        LIMIT 3
    ");
    $ultimos_clientes = $query_clientes->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($ultimos_clientes as $cliente) {
        $atividades[] = [
            'icone' => 'bi-person-plus',
            'cor' => 'success',
            'titulo' => 'Novo Cliente',
            'descricao' => $cliente['nome'] . ' cadastrado no sistema',
            'tempo' => 'Hoje'
        ];
    }

    $response = [
        'success' => true,
        'atividades' => $atividades
    ];
    
} catch (PDOException $e) {
    $response = [
        'success' => false,
        'message' => 'Erro ao carregar atividades: ' . $e->getMessage()
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
?>