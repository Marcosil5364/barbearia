<?php
session_start();
require_once("../conexao.php");

// Verificar se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado!']);
    exit();
}

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID da venda não informado!']);
    exit();
}

$id = $_GET['id'];

try {
    // Buscar dados detalhados da venda
    $query = $pdo->prepare("
        SELECT 
            v.*,
            c.nome as nome_cliente,
            c.telefone as telefone_cliente,
            c.email as email_cliente,
            p.nome as nome_produto,
            p.descricao as descricao_produto,
            p.categoria as categoria_produto,
            p.preco_venda as preco_venda_produto,
            u.nome as nome_usuario,
            DATE_FORMAT(v.data_venda, '%d/%m/%Y %H:%i') as data_venda_formatada,
            DATE_FORMAT(v.data_registro, '%d/%m/%Y %H:%i') as data_registro_formatada
        FROM vendas v
        LEFT JOIN clientes c ON v.cliente_id = c.id
        LEFT JOIN produtos p ON v.produto_id = p.id
        LEFT JOIN usuarios u ON v.usuario_id = u.id
        WHERE v.id = ?
    ");
    
    $query->execute([$id]);
    $venda = $query->fetch(PDO::FETCH_ASSOC);
    
    if (!$venda) {
        echo json_encode(['success' => false, 'message' => 'Venda não encontrada!']);
        exit();
    }
    
    // Formatar valores monetários
    $venda['valor_unitario_formatado'] = 'R$ ' . number_format($venda['valor_unitario'], 2, ',', '.');
    $venda['valor_total_formatado'] = 'R$ ' . number_format($venda['valor_total'], 2, ',', '.');
    $venda['preco_venda_produto_formatado'] = 'R$ ' . number_format($venda['preco_venda_produto'], 2, ',', '.');
    
    // Calcular diferença entre preço de venda e valor unitário
    $diferenca = $venda['valor_unitario'] - $venda['preco_venda_produto'];
    $venda['diferenca_preco'] = number_format(abs($diferenca), 2, ',', '.');
    $venda['tipo_diferenca'] = ($diferenca >= 0) ? 'acima' : 'abaixo';
    
    // Buscar histórico de vendas do mesmo cliente (últimas 5)
    $query_historico_cliente = $pdo->prepare("
        SELECT 
            v.id,
            p.nome as nome_produto,
            v.quantidade,
            v.valor_total,
            DATE_FORMAT(v.data_venda, '%d/%m/%Y') as data_venda_formatada
        FROM vendas v
        LEFT JOIN produtos p ON v.produto_id = p.id
        WHERE v.cliente_id = ? AND v.id != ?
        ORDER BY v.data_venda DESC
        LIMIT 5
    ");
    
    $query_historico_cliente->execute([$venda['cliente_id'], $id]);
    $historico_cliente = $query_historico_cliente->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar valores do histórico
    foreach ($historico_cliente as &$item) {
        $item['valor_total_formatado'] = 'R$ ' . number_format($item['valor_total'], 2, ',', '.');
    }
    
    // Buscar outras vendas do mesmo produto (últimas 5)
    $query_historico_produto = $pdo->prepare("
        SELECT 
            v.id,
            c.nome as nome_cliente,
            v.quantidade,
            v.valor_total,
            DATE_FORMAT(v.data_venda, '%d/%m/%Y') as data_venda_formatada
        FROM vendas v
        LEFT JOIN clientes c ON v.cliente_id = c.id
        WHERE v.produto_id = ? AND v.id != ?
        ORDER BY v.data_venda DESC
        LIMIT 5
    ");
    
    $query_historico_produto->execute([$venda['produto_id'], $id]);
    $historico_produto = $query_historico_produto->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar valores do histórico do produto
    foreach ($historico_produto as &$item) {
        $item['valor_total_formatado'] = 'R$ ' . number_format($item['valor_total'], 2, ',', '.');
    }
    
    echo json_encode([
        'success' => true,
        'venda' => $venda,
        'historico_cliente' => $historico_cliente,
        'historico_produto' => $historico_produto,
        'cliente_tem_historico' => count($historico_cliente) > 0,
        'produto_tem_historico' => count($historico_produto) > 0
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao buscar detalhes da venda ID {$id}: " . $e->getMessage());
    
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao buscar detalhes da venda: ' . $e->getMessage()
    ]);
}
?>