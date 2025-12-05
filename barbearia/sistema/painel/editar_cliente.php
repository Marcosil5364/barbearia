<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

if (isset($_GET['id'])) {
    $id_cliente = $_GET['id'];
    
    $query = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
    $query->execute([$id_cliente]);
    $cliente = $query->fetch(PDO::FETCH_ASSOC);
    
    if ($cliente) {
        $response = [
            'success' => true,
            'cliente' => $cliente
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $data_nascimento = $_POST['data_nascimento'];
    $observacao = $_POST['observacao'];
    
    try {
        $query = $pdo->prepare("UPDATE clientes SET nome = ?, telefone = ?, email = ?, data_nascimento = ?, observacao = ? WHERE id = ?");
        $result = $query->execute([$nome, $telefone, $email, $data_nascimento, $observacao, $id]);
        
        if ($result) {
            $response = [
                'success' => true,
                'message' => 'Cliente atualizado com sucesso!'
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Erro ao atualizar cliente!'
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