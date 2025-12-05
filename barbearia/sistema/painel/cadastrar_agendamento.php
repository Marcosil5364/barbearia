<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cliente_id = $_POST['cliente_id'];
    $data_agendamento = $_POST['data_agendamento'];
    $hora_agendamento = $_POST['hora_agendamento'];
    $servico = $_POST['servico'];
    $tecnico = $_POST['tecnico'];
    $valor = $_POST['valor'];
    $observacao = $_POST['observacao'];
    
    $query = $pdo->prepare("INSERT INTO agendamentos SET cliente_id = ?, data_agendamento = ?, hora_agendamento = ?, servico = ?, tecnico = ?, valor = ?, observacao = ?");
    
    if ($query->execute([$cliente_id, $data_agendamento, $hora_agendamento, $servico, $tecnico, $valor, $observacao])) {
        header('Location: painel.php?success=Agendamento cadastrado com sucesso!');
    } else {
        header('Location: painel.php?error=Erro ao cadastrar agendamento!');
    }
    exit();
}
?>