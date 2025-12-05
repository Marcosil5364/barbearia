<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

try {
    // Verificar se a tabela existe
    $tabela_existe = $pdo->query("SHOW TABLES LIKE 'produtos'")->rowCount() > 0;
    
    if (!$tabela_existe) {
        echo json_encode([
            'success' => true,
            'produtos' => []
        ]);
        exit;
    }

    $filtro_ativos = isset($_GET['ativos']) ? "WHERE status = 'Ativo'" : "";
    
    $query = $pdo->query("SELECT * FROM produtos $filtro_ativos ORDER BY nome");
    $produtos = $query->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'produtos' => $produtos
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar produtos: ' . $e->getMessage()
    ]);
}
?>