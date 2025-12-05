<?php
session_start();
require_once("../conexao.php");

// Verificar se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit();
}

// Buscar dados do usuário para edição
if (isset($_GET['id'])) {
    $id_usuario = $_GET['id'];
    
    try {
        // Buscar dados do usuário
        $query = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $query->execute([$id_usuario]);
        $usuario = $query->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            $response = [
                'success' => true,
                'usuario' => [
                    'id' => $usuario['id'],
                    'nome' => $usuario['nome'],
                    'email' => $usuario['email'],
                    'cpf' => $usuario['cpf'],
                    'nivel' => $usuario['nivel'],
                    'data_cadastro' => $usuario['data']
                ]
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Usuário não encontrado!'
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

// Processar atualização do usuário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $nivel = $_POST['nivel'];
    
    try {
        // Verificar se email já existe (excluindo o usuário atual)
        $query_verifica = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $query_verifica->execute([$email, $id]);
        $email_existe = $query_verifica->fetch(PDO::FETCH_ASSOC);
        
        if ($email_existe) {
            $response = [
                'success' => false,
                'message' => 'Este email já está em uso por outro usuário!'
            ];
        } else {
            if (!empty($senha)) {
                // Se a senha foi preenchida, atualizar com nova senha
                $senha_crip = md5($senha);
                $query = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, senha = ?, senha_crip = ?, nivel = ? WHERE id = ?");
                $result = $query->execute([$nome, $email, $senha, $senha_crip, $nivel, $id]);
            } else {
                // Se a senha não foi preenchida, manter a senha atual
                $query = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, nivel = ? WHERE id = ?");
                $result = $query->execute([$nome, $email, $nivel, $id]);
            }
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Usuário atualizado com sucesso!'
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Nenhuma alteração foi feita!'
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