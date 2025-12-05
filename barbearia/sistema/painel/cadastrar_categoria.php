<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    
    $query = $pdo->prepare("INSERT INTO categorias SET nome = ?, descricao = ?, data_cadastro = CURDATE()");
    
    if ($query->execute([$nome, $descricao])) {
        header('Location: painel.php?success=Categoria cadastrada com sucesso!');
    } else {
        header('Location: painel.php?error=Erro ao cadastrar categoria!');
    }
    exit();
}
?>