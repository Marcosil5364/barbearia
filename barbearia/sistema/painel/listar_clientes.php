    <?php
    session_start();
    require_once("../conexao.php");

    // Verificar se o usuário está logado
    if (!isset($_SESSION['id_usuario'])) {
        header('Location: index.php');
        exit();
    }

    try {
        // Buscar clientes
        $query = $pdo->query("
            SELECT id, nome 
            FROM clientes 
            ORDER BY nome
        ");
        $clientes = $query->fetchAll(PDO::FETCH_ASSOC);
        
        $response = [
            'success' => true,
            'clientes' => $clientes
        ];
        
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Erro ao carregar clientes: ' . $e->getMessage()
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    ?>