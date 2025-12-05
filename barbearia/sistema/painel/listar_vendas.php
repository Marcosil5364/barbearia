<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

try {
    // Verificar se a tabela vendas existe
    $tabela_existe = $pdo->query("SHOW TABLES LIKE 'vendas'")->rowCount() > 0;
    
    if (!$tabela_existe) {
        echo json_encode([
            'success' => true,
            'vendas' => []
        ]);
        exit;
    }

    $query = $pdo->query("
        SELECT v.*, c.nome as nome_cliente, p.nome as nome_produto 
        FROM vendas v 
        LEFT JOIN clientes c ON v.cliente_id = c.id 
        LEFT JOIN produtos p ON v.produto_id = p.id 
        ORDER BY v.data_venda DESC
    ");
    $vendas = $query->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'vendas' => $vendas
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar vendas: ' . $e->getMessage()
    ]);
}
?>