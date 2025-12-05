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
    // Iniciar transação
    $pdo->beginTransaction();
    
    // 1. Buscar informações da venda antes de excluir
    $query_venda = $pdo->prepare("
        SELECT v.*, p.quantidade_estoque, p.nome as nome_produto, c.nome as nome_cliente
        FROM vendas v
        LEFT JOIN produtos p ON v.produto_id = p.id
        LEFT JOIN clientes c ON v.cliente_id = c.id
        WHERE v.id = ?
    ");
    $query_venda->execute([$id]);
    $venda = $query_venda->fetch(PDO::FETCH_ASSOC);
    
    if (!$venda) {
        throw new Exception('Venda não encontrada!');
    }
    
    // 2. Reestocar o produto
    if ($venda['produto_id']) {
        $query_reestoque = $pdo->prepare("
            UPDATE produtos 
            SET quantidade_estoque = quantidade_estoque + ?,
                data_atualizacao = NOW()
            WHERE id = ?
        ");
        $query_reestoque->execute([$venda['quantidade'], $venda['produto_id']]);
        
        // Verificar se a atualização foi bem-sucedida
        if ($query_reestoque->rowCount() == 0) {
            throw new Exception('Erro ao reestocar produto!');
        }
    }
    
    // 3. Registrar na tabela de histórico (opcional, mas recomendado)
    $query_historico = $pdo->prepare("
        INSERT INTO historico_exclusoes 
        (tabela, registro_id, dados, motivo, usuario_id, data_exclusao)
        VALUES 
        ('vendas', ?, ?, 'Exclusão via painel', ?, NOW())
    ");
    
    $dados_venda = json_encode([
        'id' => $venda['id'],
        'cliente' => $venda['nome_cliente'],
        'produto' => $venda['nome_produto'],
        'quantidade' => $venda['quantidade'],
        'valor_unitario' => $venda['valor_unitario'],
        'valor_total' => $venda['valor_total'],
        'data_venda' => $venda['data_venda'],
        'observacao' => $venda['observacao']
    ]);
    
    $query_historico->execute([$id, $dados_venda, $_SESSION['id_usuario']]);
    
    // 4. Excluir a venda
    $query_excluir = $pdo->prepare("DELETE FROM vendas WHERE id = ?");
    $query_excluir->execute([$id]);
    
    if ($query_excluir->rowCount() == 0) {
        throw new Exception('Erro ao excluir venda!');
    }
    
    // Commit da transação
    $pdo->commit();
    
    // Registrar atividade
    registrarAtividade($_SESSION['id_usuario'], 'exclusao_venda', 
        "Venda excluída: {$venda['nome_produto']} para {$venda['nome_cliente']}");
    
    echo json_encode([
        'success' => true, 
        'message' => 'Venda excluída com sucesso! O estoque foi reestocado.'
    ]);
    
} catch (Exception $e) {
    // Rollback em caso de erro
    $pdo->rollBack();
    
    // Registrar erro
    error_log("Erro ao excluir venda ID {$id}: " . $e->getMessage());
    
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao excluir venda: ' . $e->getMessage()
    ]);
}

// Função para registrar atividade (se existir no seu sistema)
function registrarAtividade($usuario_id, $tipo, $descricao) {
    global $pdo;
    
    try {
        $query = $pdo->prepare("
            INSERT INTO atividades 
            (usuario_id, tipo, descricao, data_atividade)
            VALUES (?, ?, ?, NOW())
        ");
        $query->execute([$usuario_id, $tipo, $descricao]);
    } catch (Exception $e) {
        // Silenciar erro na atividade, não interromper o fluxo principal
        error_log("Erro ao registrar atividade: " . $e->getMessage());
    }
}
?>