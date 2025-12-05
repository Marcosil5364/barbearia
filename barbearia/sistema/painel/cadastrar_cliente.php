<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $data_nascimento = $_POST['data_nascimento'];
    $observacao = $_POST['observacao'];
    
    $query = $pdo->prepare("INSERT INTO clientes SET nome = ?, telefone = ?, email = ?, data_nascimento = ?, observacao = ?, data_cadastro = CURDATE()");
    
    if ($query->execute([$nome, $telefone, $email, $data_nascimento, $observacao])) {
        header('Location: painel.php?success=Cliente cadastrado com sucesso!');
    } else {
        header('Location: painel.php?error=Erro ao cadastrar cliente!');
    }
    exit();
}
?>