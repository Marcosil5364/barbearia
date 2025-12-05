<?php
session_start();
require_once("../conexao.php");

// Verificar se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit();
}

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
                    'data_cadastro' => $usuario['data'],
                    'senha' => $usuario['senha']
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
?>