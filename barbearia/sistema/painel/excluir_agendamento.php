<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

if (isset($_GET['id'])) {
    $id_agendamento = $_GET['id'];
    
    try {
        $query_excluir = $pdo->prepare("DELETE FROM agendamentos WHERE id = ?");
        if ($query_excluir->execute([$id_agendamento])) {
            $response = [
                'success' => true,
                'message' => 'Agendamento excluído com sucesso!'
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Erro ao excluir agendamento!'
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