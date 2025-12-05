<?php
session_start();
require_once("../conexao.php");

// Verificar se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// Buscar dados do usuário logado
$query = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$query->execute([$id_usuario]);
$usuario = $query->fetch(PDO::FETCH_ASSOC);

// Processar atualização do perfil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    
    try {
        if (!empty($senha)) {
            // Se a senha foi preenchida, atualizar com nova senha
            $senha_crip = md5($senha);
            $query = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, senha = ?, senha_crip = ? WHERE id = ?");
            $result = $query->execute([$nome, $email, $senha, $senha_crip, $id_usuario]);
        } else {
            // Se a senha não foi preenchida, manter a senha atual
            $query = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ? WHERE id = ?");
            $result = $query->execute([$nome, $email, $id_usuario]);
        }
        
        if ($result) {
            // Atualizar dados na sessão
            $_SESSION['nome_usuario'] = $nome;
            $_SESSION['email_usuario'] = $email;
            
            $response = [
                'success' => true,
                'message' => 'Perfil atualizado com sucesso!',
                'nome' => $nome,
                'email' => $email
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Erro ao atualizar perfil!'
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

// Se não for POST, retornar dados do usuário
header('Content-Type: application/json');
echo json_encode([
    'id' => $usuario['id'],
    'nome' => $usuario['nome'],
    'email' => $usuario['email'],
    'nivel' => $usuario['nivel'],
    'data_cadastro' => $usuario['data']
]);
exit();
?>