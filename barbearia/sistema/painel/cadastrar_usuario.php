<?php
session_start();
require_once("../conexao.php");

// Verificar se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $nivel = $_POST['nivel'];
    $senha_crip = md5($senha);
    
    try {
        $query = $pdo->prepare("INSERT INTO usuarios SET nome = ?, email = ?, senha = ?, senha_crip = ?, nivel = ?, data = CURDATE()");
        $query->execute([$nome, $email, $senha, $senha_crip, $nivel]);
        
        header('Location: painel.php?success=Usuário cadastrado com sucesso!');
        exit();
    } catch (PDOException $e) {
        header('Location: painel.php?error=Erro ao cadastrar usuário: ' . $e->getMessage());
        exit();
    }
} else {
    header('Location: painel.php');
    exit();
}
?>