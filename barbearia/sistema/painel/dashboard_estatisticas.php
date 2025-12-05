<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

try {
    // Total de clientes
    $query_clientes = $pdo->query("SELECT COUNT(*) as total FROM clientes");
    $total_clientes = $query_clientes->fetch(PDO::FETCH_ASSOC)['total'];

    // Agendamentos de hoje
    $hoje = date('Y-m-d');
    $query_agendamentos_hoje = $pdo->prepare("SELECT COUNT(*) as total FROM agendamentos WHERE data_agendamento = ?");
    $query_agendamentos_hoje->execute([$hoje]);
    $agendamentos_hoje = $query_agendamentos_hoje->fetch(PDO::FETCH_ASSOC)['total'];

    // Serviços do mês (Concluídos)
    $mes_atual = date('Y-m');
    $query_servicos_mes = $pdo->prepare("SELECT COUNT(*) as total FROM agendamentos WHERE DATE_FORMAT(data_agendamento, '%Y-%m') = ? AND status = 'Concluído'");
    $query_servicos_mes->execute([$mes_atual]);
    $servicos_mes = $query_servicos_mes->fetch(PDO::FETCH_ASSOC)['total'];

    // Faturamento do mês
    $query_faturamento = $pdo->prepare("SELECT COALESCE(SUM(valor), 0) as total FROM agendamentos WHERE DATE_FORMAT(data_agendamento, '%Y-%m') = ? AND status = 'Concluído'");
    $query_faturamento->execute([$mes_atual]);
    $faturamento_mes = $query_faturamento->fetch(PDO::FETCH_ASSOC)['total'];

    // Verificar se a tabela produtos existe
    $tabela_existe = $pdo->query("SHOW TABLES LIKE 'produtos'")->rowCount() > 0;
    
    $total_produtos = 0;
    $estoque_baixo = 0;

    if ($tabela_existe) {
        // Total de produtos
        $query_produtos = $pdo->query("SELECT COUNT(*) as total FROM produtos");
        $total_produtos = $query_produtos->fetch(PDO::FETCH_ASSOC)['total'];

        // Produtos com estoque baixo
        $query_estoque_baixo = $pdo->query("SELECT COUNT(*) as total FROM produtos WHERE quantidade_estoque <= quantidade_minima AND status = 'Ativo'");
        $estoque_baixo = $query_estoque_baixo->fetch(PDO::FETCH_ASSOC)['total'];
    }

    echo json_encode([
        'success' => true,
        'total_clientes' => (int)$total_clientes,
        'agendamentos_hoje' => (int)$agendamentos_hoje,
        'servicos_mes' => (int)$servicos_mes,
        'faturamento_mes' => number_format($faturamento_mes, 2, '.', ''),
        'total_produtos' => (int)$total_produtos,
        'estoque_baixo' => (int)$estoque_baixo
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar estatísticas: ' . $e->getMessage()
    ]);
}
?>