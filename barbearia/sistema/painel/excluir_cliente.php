<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

if (isset($_GET['id'])) {
    $id_cliente = $_GET['id'];
    
    try {
        // Verificar se o cliente tem agendamentos
        $query_verifica = $pdo->prepare("SELECT COUNT(*) as total FROM agendamentos WHERE cliente_id = ?");
        $query_verifica->execute([$id_cliente]);
        $total_agendamentos = $query_verifica->fetch(PDO::FETCH_ASSOC)['total'];
        
        if ($total_agendamentos > 0) {
            $response = [
                'success' => false,
                'message' => 'Não é possível excluir o cliente pois existem agendamentos vinculados a ele!'
            ];
        } else {
            $query_excluir = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
            if ($query_excluir->execute([$id_cliente])) {
                $response = [
                    'success' => true,
                    'message' => 'Cliente excluído com sucesso!'
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Erro ao excluir cliente!'
                ];
            }
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