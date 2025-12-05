<?php
session_start();
require_once("conexao.php");

// Verificar se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Usar 'username' em vez de 'email' porque no seu form o campo é name="username"
    $username = $_POST['username'];
    $senha = $_POST['password'];
    
    // Criptografar a senha com MD5 para comparar com o banco
    $senha_crip = md5($senha);

    // Verificar se o username é email ou CPF
    if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
        // É email
        $query = $pdo->prepare("SELECT * FROM usuarios WHERE (email = ? OR email = ?) AND senha_crip = ?");
        $query->execute([$username, $nome_sistema, $senha_crip]);
    } else {
        // É CPF - verificar tanto o CPF quanto o email do sistema
        $query = $pdo->prepare("SELECT * FROM usuarios WHERE (cpf = ? OR email = ?) AND senha_crip = ?");
        $query->execute([$username, $nome_sistema, $senha_crip]);
    }

    $res = $query->fetchAll(PDO::FETCH_ASSOC);
    $total_reg = count($res);
    $ativo = $res[0]['ativo'] ?? 'Não';

    if ($total_reg > 0) {
        // Login bem-sucedido
        $_SESSION['id_usuario'] = $res[0]['id'];
        $_SESSION['nome_usuario'] = $res[0]['nome'];
        $_SESSION['nivel_usuario'] = $res[0]['nivel'];
        $_SESSION['email_usuario'] = $res[0]['email'];
        $_SESSION['ativo_usuario'] = $ativo;
        
        header('Location: painel/painel.php');
        exit();
    } else {
        // Login falhou
        header('Location: index.php?error=Usuário ou senha incorretos');
        exit();
    }
} else {
    // Se não foi POST, redireciona para index
    header('Location: index.php');
    exit();
}
?>