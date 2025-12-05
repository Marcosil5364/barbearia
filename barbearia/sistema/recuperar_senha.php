<?php
require_once("conexao.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    
    // Verificar se o email existe no banco de dados
    $query = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $query->execute([$email]);
    $res = $query->fetchAll(PDO::FETCH_ASSOC);
    $total_reg = count($res);
    
    if ($total_reg > 0) {
        // Email encontrado - Aqui você pode implementar:
        // 1. Gerar token de recuperação
        // 2. Enviar email com link de recuperação
        // 3. Salvar token no banco de dados
        
        // Por enquanto, vou apenas mostrar uma mensagem
        $mensagem = "Instruções de recuperação enviadas para: " . htmlspecialchars($email);
        header('Location: index.php?success=' . urlencode($mensagem));
        exit();
    } else {
        // Email não encontrado
        $mensagem = "E-mail não encontrado em nosso sistema.";
        header('Location: index.php?error=' . urlencode($mensagem));
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
?>