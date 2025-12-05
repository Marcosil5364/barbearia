<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

if (isset($_GET['id'])) {
    $id_agendamento = $_GET['id'];
    
    $query = $pdo->prepare("SELECT a.*, c.nome as nome_cliente FROM agendamentos a LEFT JOIN clientes c ON a.cliente_id = c.id WHERE a.id = ?");
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $cliente_id = $_POST['cliente_id'];
    $data_agendamento = $_POST['data_agendamento'];
    $hora_agendamento = $_POST['hora_agendamento'];
    $servico = $_POST['servico'];
    $tecnico = $_POST['tecnico'];
    $valor = $_POST['valor'];
    $status = $_POST['status'];
    $observacao = $_POST['observacao'];
    
    try {
        $query = $pdo->prepare("UPDATE agendamentos SET cliente_id = ?, data_agendamento = ?, hora_agendamento = ?, servico = ?, tecnico = ?, valor = ?, status = ?, observacao = ? WHERE id = ?");
        $result = $query->execute([$cliente_id, $data_agendamento, $hora_agendamento, $servico, $tecnico, $valor, $status, $observacao, $id]);
        
        if ($result) {
            $response = [
                'success' => true,
                'message' => 'Agendamento atualizado com sucesso!'
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Erro ao atualizar agendamento!'
            ];
        }
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Erro no banco de dados: ' . $e->getMessage()
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>