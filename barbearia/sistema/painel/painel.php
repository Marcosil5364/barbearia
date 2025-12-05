<?php
session_start();
require_once("../conexao.php");

// Verificar se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

// Buscar dados dos usuários para a tabela
$query_usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY id DESC");
$usuarios = $query_usuarios->fetchAll(PDO::FETCH_ASSOC);

// Buscar dados para as tabelas de clientes e agendamentos
$query_clientes = $pdo->query("
    SELECT c.*, 
           COUNT(a.id) as total_servicos,
           (SELECT tecnico FROM agendamentos WHERE cliente_id = c.id ORDER BY id DESC LIMIT 1) as ultimo_tecnico
    FROM clientes c 
    LEFT JOIN agendamentos a ON c.id = a.cliente_id 
    GROUP BY c.id 
    ORDER BY c.nome
");
$clientes_tabela = $query_clientes->fetchAll(PDO::FETCH_ASSOC);

$query_agendamentos = $pdo->query("
    SELECT a.*, c.nome as nome_cliente 
    FROM agendamentos a 
    LEFT JOIN clientes c ON a.cliente_id = c.id 
    ORDER BY a.data_agendamento DESC, a.hora_agendamento DESC
");
$agendamentos_tabela = $query_agendamentos->fetchAll(PDO::FETCH_ASSOC);

// Processar exclusão de usuário
if (isset($_GET['excluir_id'])) {
    $excluir_id = $_GET['excluir_id'];
    
    // Impedir que o usuário exclua a si mesmo
    if ($excluir_id != $_SESSION['id_usuario']) {
        $query_excluir = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        if ($query_excluir->execute([$excluir_id])) {
            header('Location: painel.php?success=Usuário excluído com sucesso!');
            exit();
        } else {
            header('Location: painel.php?error=Erro ao excluir usuário!');
            exit();
        }
    } else {
        header('Location: painel.php?error=Você não pode excluir seu próprio usuário!');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel - <?php echo $nome_sistema ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="../img/favicon.ico">
    <style>
        .sidebar {
            background-color: #343a40;
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding-top: 60px;
            width: 250px;
        }
        
        .sidebar .nav-link {
            color: #adb5bd;
            padding: 12px 20px;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover {
            color: white;
            background-color: #495057;
            border-left-color: #007bff;
        }
        
        .sidebar .nav-link.active {
            color: white;
            background-color: #495057;
            border-left-color: #007bff;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .navbar {
            margin-left: 250px;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }
        
        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        
        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
        }
        
        /* Alertas personalizados */
        .alert {
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        /* Dashboard styles */
        .card-dashboard {
            transition: transform 0.2s;
        }
        
        .card-dashboard:hover {
            transform: translateY(-5px);
        }
        
        .progress {
            background-color: #e9ecef;
        }
        
        /* Estilo para alerta flutuante */
        .alert-floating {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="text-center mb-4">
            <div class="user-avatar mx-auto mb-2">
                <img src="../img/favicon.ico" alt="Avatar" style="width: 35px; height: 35px; border-radius: 50%;">
            </div>
            <h6 class="text-white"><?php echo 'JK BARBERSHOP'; ?></h6>
            <small class="text-white"><?php echo $_SESSION['nivel_usuario']; ?></small>
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link active" href="#" id="nav-home">
                <i class="bi bi-house me-2"></i>Home
            </a>
            <a class="nav-link" href="#" id="nav-pessoas">
                <i class="bi bi-people me-2"></i>Pessoas
            </a>
            <a class="nav-link" href="#" id="nav-clientes">
                <i class="bi bi-person me-2"></i>Clientes
            </a>
            <a class="nav-link" href="#" id="nav-agendamentos">
                <i class="bi bi-calendar me-2"></i>Agendamentos
            </a>
            <a class="nav-link" href="#" id="nav-produtos">
                <i class="bi bi-box me-2"></i>Produtos
            </a>
            <a class="nav-link" href="#" id="nav-vendas">
                <i class="bi bi-cart me-2"></i>Vendas
            </a>
        </nav>
    </div>

    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand"><?php echo $nome_sistema ?></span>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <div class="user-avatar me-2">
                            <img src="../img/favicon.ico" alt="Avatar" style="width: 30px; height: 30px; border-radius: 50%;">
                        </div>
                        <span><?php echo $_SESSION['nome_usuario']; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" onclick="abrirEditarPerfil()"><i class="bi bi-person me-2"></i>Meu Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Alertas de sucesso/erro -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_GET['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_GET['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Home Section - Dashboard -->
        <div id="home-section">
            <div class="row">
                <!-- Cards de Estatísticas -->
                <div class="col-md-3 mb-4">
                    <div class="card bg-primary text-white card-dashboard">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0" id="total-clientes-dash">0</h4>
                                    <p class="mb-0">Total Clientes</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-people fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card bg-success text-white card-dashboard">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0" id="total-agendamentos-dash">0</h4>
                                    <p class="mb-0">Agendamentos Hoje</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-calendar-check fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card bg-warning text-dark card-dashboard">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0" id="total-servicos-dash">0</h4>
                                    <p class="mb-0">Serviços Mês</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-scissors fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card bg-info text-white card-dashboard">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0" id="faturamento-dash">R$ 0</h4>
                                    <p class="mb-0">Faturamento Mês</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-currency-dollar fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Novos cards para produtos -->
                <div class="col-md-3 mb-4">
                    <div class="card bg-secondary text-white card-dashboard">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0" id="total-produtos-dash">0</h4>
                                    <p class="mb-0">Total Produtos</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-box fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="card bg-danger text-white card-dashboard">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0" id="estoque-baixo-dash">0</h4>
                                    <p class="mb-0">Estoque Baixo</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-exclamation-triangle fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Informações do Usuário -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-person me-2"></i>Meus Dados</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Nome:</strong> <?php echo htmlspecialchars($_SESSION['nome_usuario']); ?></p>
                                    <p><strong>Email:</strong> <?php echo $_SESSION['email_usuario']; ?></p>
                                    <p><strong>Nível:</strong> <span class="badge bg-success"><?php echo $_SESSION['nivel_usuario']; ?></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Último Acesso:</strong> <br><span id="ultimo-acesso"><?php echo date('d/m/Y H:i:s'); ?></span></p>
                                    <p><strong>Status:</strong> <span class="badge bg-success">Online</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Agendamentos do Dia -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-calendar-day me-2"></i>Agendamentos de Hoje</h5>
                        </div>
                        <div class="card-body">
                            <div id="agendamentos-hoje">
                                <div class="text-center">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Carregando...</span>
                                    </div>
                                    <small>Carregando agendamentos...</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Gráfico de Serviços Mais Populares -->
                <div class="col-md-8 mb-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Serviços Mais Populares</h5>
                        </div>
                        <div class="card-body">
                            <div id="grafico-servicos" style="height: 300px;">
                                <div class="text-center py-5">
                                    <div class="spinner-border" role="status">
                                        <span class="visually-hidden">Carregando...</span>
                                    </div>
                                    <p class="mt-2">Carregando gráfico...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Últimos Clientes Cadastrados -->
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Últimos Clientes</h5>
                        </div>
                        <div class="card-body">
                            <div id="ultimos-clientes">
                                <div class="text-center">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Carregando...</span>
                                    </div>
                                    <small>Carregando clientes...</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Atividade Recente -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Atividade Recente</h5>
                        </div>
                        <div class="card-body">
                            <div id="atividade-recente">
                                <div class="text-center">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Carregando...</span>
                                    </div>
                                    <small>Carregando atividades...</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pessoas Section (Hidden by default) -->
        <div id="pessoas-section" style="display: none;">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="bi bi-people me-2"></i>Gerenciar Usuários</h4>
                            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalNovoUsuario">
                                <i class="bi bi-plus-circle me-1"></i>Novo Usuário
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>Email</th>
                                            <th>Senha</th>
                                            <th>Nível</th>
                                            <th>Cadastro</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($usuarios as $usuario): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                            <td>
                                                <span class="badge bg-secondary" title="<?php echo htmlspecialchars($usuario['senha']); ?>">
                                                    ••••••••
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $usuario['nivel'] == 'Administrador' ? 'bg-primary' : 'bg-info'; ?>">
                                                    <?php echo $usuario['nivel']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($usuario['data'])); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" title="Editar" onclick="editarUsuario(<?php echo $usuario['id']; ?>)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-outline-info" title="Informações" onclick="verInformacoes(<?php echo $usuario['id']; ?>)">
                                                        <i class="bi bi-info-circle"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" title="Excluir" onclick="confirmarExclusao(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['nome']); ?>')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clientes Section (Hidden by default) -->
        <div id="clientes-section" style="display: none;">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="bi bi-person me-2"></i>Gerenciar Clientes</h4>
                            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalNovoCliente">
                                <i class="bi bi-plus-circle me-1"></i>Novo Cliente
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small><strong><?php echo count($clientes_tabela); ?> registros</strong></small>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>Telefone</th>
                                            <th>Cadastro</th>
                                            <th>Nascimento</th>
                                            <th>Técnico</th>
                                            <th>Cartão</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($clientes_tabela as $cliente): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($cliente['nome']); ?></td>
                                            <td><?php echo htmlspecialchars($cliente['telefone']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($cliente['data_cadastro'])); ?></td>
                                            <td><?php echo $cliente['data_nascimento'] ? date('d/m/Y', strtotime($cliente['data_nascimento'])) : 'Sem Lançamento'; ?></td>
                                            <td><?php echo $cliente['ultimo_tecnico'] ?: '0'; ?></td>
                                            <td><?php echo $cliente['total_servicos'] ?: '0'; ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" title="Editar" onclick="editarCliente(<?php echo $cliente['id']; ?>)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-outline-info" title="Informações" onclick="verInformacoesCliente(<?php echo $cliente['id']; ?>)">
                                                        <i class="bi bi-info-circle"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" title="Excluir" onclick="confirmarExclusaoCliente(<?php echo $cliente['id']; ?>, '<?php echo htmlspecialchars($cliente['nome']); ?>')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Agendamentos Section (Hidden by default) -->
        <div id="agendamentos-section" style="display: none;">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="bi bi-calendar me-2"></i>Agendamentos/Serviços</h4>
                            <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#modalNovoAgendamento">
                                <i class="bi bi-plus-circle me-1"></i>Novo Agendamento
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Data</th>
                                            <th>Hora</th>
                                            <th>Serviço</th>
                                            <th>Técnico</th>
                                            <th>Valor</th>
                                            <th>Status</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($agendamentos_tabela as $agendamento): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($agendamento['nome_cliente']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($agendamento['data_agendamento'])); ?></td>
                                            <td><?php echo $agendamento['hora_agendamento']; ?></td>
                                            <td><?php echo htmlspecialchars($agendamento['servico']); ?></td>
                                            <td><?php echo htmlspecialchars($agendamento['tecnico'] ?: ''); ?></td>
                                            <td>R$ <?php echo number_format($agendamento['valor'], 2, ',', '.'); ?></td>
                                            <td>
                                                <span class="badge <?php echo $agendamento['status'] === 'Concluído' ? 'bg-success' : ($agendamento['status'] === 'Cancelado' ? 'bg-danger' : 'bg-warning'); ?>">
                                                    <?php echo $agendamento['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" title="Editar" onclick="editarAgendamento(<?php echo $agendamento['id']; ?>)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-outline-info" title="Informações" onclick="verInformacoesAgendamento(<?php echo $agendamento['id']; ?>)">
                                                        <i class="bi bi-info-circle"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" title="Excluir" onclick="confirmarExclusaoAgendamento(<?php echo $agendamento['id']; ?>, '<?php echo htmlspecialchars($agendamento['servico']); ?>')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Produtos Section (Hidden by default) -->
        <div id="produtos-section" style="display: none;">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="bi bi-box me-2"></i>Gerenciar Produtos</h4>
                            <div>
                                <button class="btn btn-light btn-sm me-2" data-bs-toggle="modal" data-bs-target="#modalNovoProduto">
                                    <i class="bi bi-plus-circle me-1"></i>Novo Produto
                                </button>
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEntradaEstoque">
                                    <i class="bi bi-arrow-down-circle me-1"></i>Entrada Estoque
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small><strong id="total-produtos">0 registros</strong></small>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>Categoria</th>
                                            <th>Preço Venda</th>
                                            <th>Estoque</th>
                                            <th>Status</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabela-produtos">
                                        <!-- Os produtos serão carregados via JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== SEÇÃO DE VENDAS ADICIONADA AQUI ========== -->
        <div id="vendas-section" style="display: none;">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="bi bi-cart me-2"></i>Gerenciar Vendas</h4>
                            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalNovaVenda">
                                <i class="bi bi-plus-circle me-1"></i>Nova Venda
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small><strong id="total-vendas">0 registros</strong></small>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th>Cliente</th>
                                            <th>Produto</th>
                                            <th>Quantidade</th>
                                            <th>Valor Total</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabela-vendas">
                                        <!-- As vendas serão carregadas via JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Novo Usuário -->
    <div class="modal fade" id="modalNovoUsuario" tabindex="-1" aria-labelledby="modalNovoUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNovoUsuarioLabel">Novo Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="cadastrar_usuario.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="senha" name="senha" required>
                        </div>
                        <div class="mb-3">
                            <label for="nivel" class="form-label">Nível</label>
                            <select class="form-select" id="nivel" name="nivel" required>
                                <option value="Administrador">Administrador</option>
                                <option value="Usuario">Usuário</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Cadastrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Perfil -->
    <div class="modal fade" id="modalEditarPerfil" tabindex="-1" aria-labelledby="modalEditarPerfilLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarPerfilLabel">Editar Meu Perfil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditarPerfil">
                    <div class="modal-body">
                        <div id="alertPerfil"></div>
                        <div class="mb-3">
                            <label for="edit_nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="edit_nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_senha" class="form-label">Nova Senha</label>
                            <input type="password" class="form-control" id="edit_senha" name="senha" placeholder="Deixe em branco para manter a senha atual">
                            <div class="form-text">Mínimo 6 caracteres</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nível</label>
                            <input type="text" class="form-control" id="edit_nivel" readonly style="background-color: #f8f9fa;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Data de Cadastro</label>
                            <input type="text" class="form-control" id="edit_data" readonly style="background-color: #f8f9fa;">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnSalvarPerfil">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Informações do Usuário -->
    <div class="modal fade" id="modalInformacoes" tabindex="-1" aria-labelledby="modalInformacoesLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInformacoesLabel">Informações do Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="informacoesUsuario">
                    <!-- As informações serão carregadas via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Usuário -->
    <div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarUsuarioLabel">Editar Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditarUsuario">
                    <div class="modal-body">
                        <div id="alertEditarUsuario"></div>
                        <input type="hidden" id="edit_usuario_id" name="id">
                        <div class="mb-3">
                            <label for="edit_usuario_nome" class="form-label">Nome *</label>
                            <input type="text" class="form-control" id="edit_usuario_nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_usuario_email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="edit_usuario_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_usuario_cpf" class="form-label">CPF</label>
                            <input type="text" class="form-control" id="edit_usuario_cpf" readonly style="background-color: #f8f9fa;">
                        </div>
                        <div class="mb-3">
                            <label for="edit_usuario_senha" class="form-label">Nova Senha</label>
                            <input type="password" class="form-control" id="edit_usuario_senha" name="senha" placeholder="Deixe em branco para manter a senha atual">
                            <div class="form-text">Mínimo 6 caracteres (opcional)</div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_usuario_nivel" class="form-label">Nível *</label>
                            <select class="form-select" id="edit_usuario_nivel" name="nivel" required>
                                <option value="Administrador">Administrador</option>
                                <option value="Usuario">Usuário</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Data de Cadastro</label>
                            <input type="text" class="form-control" id="edit_usuario_data" readonly style="background-color: #f8f9fa;">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnSalvarUsuario">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Novo Cliente -->
    <div class="modal fade" id="modalNovoCliente" tabindex="-1" aria-labelledby="modalNovoClienteLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNovoClienteLabel">Novo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formNovoCliente" action="cadastrar_cliente.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="cliente_nome" class="form-label">Nome *</label>
                            <input type="text" class="form-control" id="cliente_nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="cliente_telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="cliente_telefone" name="telefone" placeholder="(00) 00000-0000">
                        </div>
                        <div class="mb-3">
                            <label for="cliente_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="cliente_email" name="email">
                        </div>
                        <div class="mb-3">
                            <label for="cliente_nascimento" class="form-label">Data de Nascimento</label>
                            <input type="date" class="form-control" id="cliente_nascimento" name="data_nascimento">
                        </div>
                        <div class="mb-3">
                            <label for="cliente_observacao" class="form-label">Observação</label>
                            <textarea class="form-control" id="cliente_observacao" name="observacao" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Cadastrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Cliente -->
    <div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-labelledby="modalEditarClienteLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarClienteLabel">Editar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditarCliente">
                    <div class="modal-body">
                        <div id="alertEditarCliente"></div>
                        <input type="hidden" id="edit_cliente_id" name="id">
                        <div class="mb-3">
                            <label for="edit_cliente_nome" class="form-label">Nome *</label>
                            <input type="text" class="form-control" id="edit_cliente_nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_cliente_telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="edit_cliente_telefone" name="telefone" placeholder="(00) 00000-0000">
                        </div>
                        <div class="mb-3">
                            <label for="edit_cliente_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_cliente_email" name="email">
                        </div>
                        <div class="mb-3">
                            <label for="edit_cliente_nascimento" class="form-label">Data de Nascimento</label>
                            <input type="date" class="form-control" id="edit_cliente_nascimento" name="data_nascimento">
                        </div>
                        <div class="mb-3">
                            <label for="edit_cliente_observacao" class="form-label">Observação</label>
                            <textarea class="form-control" id="edit_cliente_observacao" name="observacao" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnSalvarCliente">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Informações Cliente -->
    <div class="modal fade" id="modalInformacoesCliente" tabindex="-1" aria-labelledby="modalInformacoesClienteLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInformacoesClienteLabel">Informações do Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="informacoesCliente">
                    <!-- As informações serão carregadas via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Novo Agendamento -->
    <div class="modal fade" id="modalNovoAgendamento" tabindex="-1" aria-labelledby="modalNovoAgendamentoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNovoAgendamentoLabel">Novo Agendamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formNovoAgendamento" action="cadastrar_agendamento.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="agendamento_cliente" class="form-label">Cliente *</label>
                            <select class="form-select" id="agendamento_cliente" name="cliente_id" required>
                                <option value="">Selecione um cliente</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="agendamento_data" class="form-label">Data *</label>
                            <input type="date" class="form-control" id="agendamento_data" name="data_agendamento" required>
                        </div>
                        <div class="mb-3">
                            <label for="agendamento_hora" class="form-label">Hora *</label>
                            <input type="time" class="form-control" id="agendamento_hora" name="hora_agendamento" required>
                        </div>
                        <div class="mb-3">
                            <label for="agendamento_servico" class="form-label">Serviço *</label>
                            <select class="form-select" id="agendamento_servico" name="servico" required>
                                <option value="">Selecione um serviço</option>
                                <option value="Corte de Cabelo">Corte de Cabelo</option>
                                <option value="Barba">Barba</option>
                                <option value="Corte + Barba">Corte + Barba</option>
                                <option value="Sobrancelha">Sobrancelha</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="agendamento_tecnico" class="form-label">Técnico</label>
                            <input type="text" class="form-control" id="agendamento_tecnico" name="tecnico" placeholder="Nome do técnico">
                        </div>
                        <div class="mb-3">
                            <label for="agendamento_valor" class="form-label">Valor</label>
                            <input type="number" class="form-control" id="agendamento_valor" name="valor" step="0.01" placeholder="0.00">
                        </div>
                        <div class="mb-3">
                            <label for="agendamento_observacao" class="form-label">Observação</label>
                            <textarea class="form-control" id="agendamento_observacao" name="observacao" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Agendar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Agendamento -->
    <div class="modal fade" id="modalEditarAgendamento" tabindex="-1" aria-labelledby="modalEditarAgendamentoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarAgendamentoLabel">Editar Agendamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditarAgendamento">
                    <div class="modal-body">
                        <div id="alertEditarAgendamento"></div>
                        <input type="hidden" id="edit_agendamento_id" name="id">
                        <div class="mb-3">
                            <label for="edit_agendamento_cliente" class="form-label">Cliente *</label>
                            <select class="form-select" id="edit_agendamento_cliente" name="cliente_id" required>
                                <option value="">Selecione um cliente</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_agendamento_data" class="form-label">Data *</label>
                            <input type="date" class="form-control" id="edit_agendamento_data" name="data_agendamento" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_agendamento_hora" class="form-label">Hora *</label>
                            <input type="time" class="form-control" id="edit_agendamento_hora" name="hora_agendamento" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_agendamento_servico" class="form-label">Serviço *</label>
                            <select class="form-select" id="edit_agendamento_servico" name="servico" required>
                                <option value="">Selecione um serviço</option>
                                <option value="Corte de Cabelo">Corte de Cabelo</option>
                                <option value="Barba">Barba</option>
                                <option value="Corte + Barba">Corte + Barba</option>
                                <option value="Sobrancelha">Sobrancelha</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_agendamento_tecnico" class="form-label">Técnico</label>
                            <input type="text" class="form-control" id="edit_agendamento_tecnico" name="tecnico" placeholder="Nome do técnico">
                        </div>
                        <div class="mb-3">
                            <label for="edit_agendamento_valor" class="form-label">Valor</label>
                            <input type="number" class="form-control" id="edit_agendamento_valor" name="valor" step="0.01" placeholder="0.00">
                        </div>
                        <div class="mb-3">
                            <label for="edit_agendamento_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_agendamento_status" name="status" required>
                                <option value="Agendado">Agendado</option>
                                <option value="Concluído">Concluído</option>
                                <option value="Cancelado">Cancelado</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_agendamento_observacao" class="form-label">Observação</label>
                            <textarea class="form-control" id="edit_agendamento_observacao" name="observacao" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnSalvarAgendamento">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Informações Agendamento -->
    <div class="modal fade" id="modalInformacoesAgendamento" tabindex="-1" aria-labelledby="modalInformacoesAgendamentoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInformacoesAgendamentoLabel">Informações do Agendamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="informacoesAgendamento">
                    <!-- As informações serão carregadas via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Novo Produto -->
    <div class="modal fade" id="modalNovoProduto" tabindex="-1" aria-labelledby="modalNovoProdutoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNovoProdutoLabel">Novo Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formNovoProduto">
                    <div class="modal-body">
                        <div id="alertNovoProduto"></div>
                        <div class="mb-3">
                            <label for="produto_nome" class="form-label">Nome *</label>
                            <input type="text" class="form-control" id="produto_nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="produto_descricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="produto_descricao" name="descricao" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="produto_categoria" class="form-label">Categoria</label>
                            <input type="text" class="form-control" id="produto_categoria" name="categoria" placeholder="Ex: Barba, Cabelo, etc.">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="produto_preco_custo" class="form-label">Preço de Custo</label>
                                    <input type="number" class="form-control" id="produto_preco_custo" name="preco_custo" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="produto_preco_venda" class="form-label">Preço de Venda *</label>
                                    <input type="number" class="form-control" id="produto_preco_venda" name="preco_venda" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="produto_quantidade" class="form-label">Quantidade Inicial</label>
                                    <input type="number" class="form-control" id="produto_quantidade" name="quantidade_estoque" min="0" value="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="produto_quantidade_minima" class="form-label">Estoque Mínimo</label>
                                    <input type="number" class="form-control" id="produto_quantidade_minima" name="quantidade_minima" min="0" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="produto_codigo_barras" class="form-label">Código de Barras</label>
                            <input type="text" class="form-control" id="produto_codigo_barras" name="codigo_barras">
                        </div>
                        <div class="mb-3">
                            <label for="produto_fornecedor" class="form-label">Fornecedor</label>
                            <input type="text" class="form-control" id="produto_fornecedor" name="fornecedor">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnSalvarProduto">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                            Cadastrar Produto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Produto -->
    <div class="modal fade" id="modalEditarProduto" tabindex="-1" aria-labelledby="modalEditarProdutoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarProdutoLabel">Editar Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditarProduto">
                    <div class="modal-body">
                        <div id="alertEditarProduto"></div>
                        <input type="hidden" id="edit_produto_id" name="id">
                        <div class="mb-3">
                            <label for="edit_produto_nome" class="form-label">Nome *</label>
                            <input type="text" class="form-control" id="edit_produto_nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_produto_descricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="edit_produto_descricao" name="descricao" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_produto_categoria" class="form-label">Categoria</label>
                            <input type="text" class="form-control" id="edit_produto_categoria" name="categoria" placeholder="Ex: Barba, Cabelo, etc.">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_produto_preco_custo" class="form-label">Preço de Custo</label>
                                    <input type="number" class="form-control" id="edit_produto_preco_custo" name="preco_custo" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_produto_preco_venda" class="form-label">Preço de Venda *</label>
                                    <input type="number" class="form-control" id="edit_produto_preco_venda" name="preco_venda" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_produto_quantidade_minima" class="form-label">Estoque Mínimo</label>
                            <input type="number" class="form-control" id="edit_produto_quantidade_minima" name="quantidade_minima" min="0" value="0">
                        </div>
                        <div class="mb-3">
                            <label for="edit_produto_codigo_barras" class="form-label">Código de Barras</label>
                            <input type="text" class="form-control" id="edit_produto_codigo_barras" name="codigo_barras">
                        </div>
                        <div class="mb-3">
                            <label for="edit_produto_fornecedor" class="form-label">Fornecedor</label>
                            <input type="text" class="form-control" id="edit_produto_fornecedor" name="fornecedor">
                        </div>
                        <div class="mb-3">
                            <label for="edit_produto_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_produto_status" name="status" required>
                                <option value="Ativo">Ativo</option>
                                <option value="Inativo">Inativo</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnSalvarEditarProduto">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Informações Produto -->
    <div class="modal fade" id="modalInformacoesProduto" tabindex="-1" aria-labelledby="modalInformacoesProdutoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInformacoesProdutoLabel">Informações do Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="informacoesProduto">
                    <!-- As informações serão carregadas via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Entrada Estoque -->
    <div class="modal fade" id="modalEntradaEstoque" tabindex="-1" aria-labelledby="modalEntradaEstoqueLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEntradaEstoqueLabel">Entrada no Estoque</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEntradaEstoque">
                    <div class="modal-body">
                        <div id="alertEntradaEstoque"></div>
                        <div class="mb-3">
                            <label for="entrada_produto" class="form-label">Produto *</label>
                            <select class="form-select" id="entrada_produto" name="produto_id" required>
                                <option value="">Selecione um produto</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="entrada_quantidade" class="form-label">Quantidade *</label>
                            <input type="number" class="form-control" id="entrada_quantidade" name="quantidade" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="entrada_preco_custo" class="form-label">Preço de Custo (Unitário)</label>
                            <input type="number" class="form-control" id="entrada_preco_custo" name="preco_custo" step="0.01" min="0">
                        </div>
                        <div class="mb-3">
                            <label for="entrada_observacao" class="form-label">Observação</label>
                            <textarea class="form-control" id="entrada_observacao" name="observacao" rows="3" placeholder="Ex: Compra do fornecedor X, entrada inicial, etc."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnSalvarEntradaEstoque">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                            Registrar Entrada
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========== MODAL NOVA VENDA ADICIONADO AQUI ========== -->
    <div class="modal fade" id="modalNovaVenda" tabindex="-1" aria-labelledby="modalNovaVendaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNovaVendaLabel">Nova Venda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formNovaVenda">
                    <div class="modal-body">
                        <div id="alertNovaVenda"></div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="venda_cliente" class="form-label">Cliente *</label>
                                    <select class="form-select" id="venda_cliente" name="cliente_id" required>
                                        <option value="">Selecione um cliente</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="venda_produto" class="form-label">Produto *</label>
                                    <select class="form-select" id="venda_produto" name="produto_id" required>
                                        <option value="">Selecione um produto</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="venda_quantidade" class="form-label">Quantidade *</label>
                                    <input type="number" class="form-control" id="venda_quantidade" name="quantidade" min="1" value="1" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="venda_valor_unitario" class="form-label">Preço Unitário *</label>
                                    <input type="number" class="form-control" id="venda_valor_unitario" name="valor_unitario" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="venda_valor_total" class="form-label">Valor Total</label>
                                    <input type="number" class="form-control" id="venda_valor_total" name="valor_total" step="0.01" readonly style="background-color: #f8f9fa;">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="venda_observacao" class="form-label">Observação</label>
                            <textarea class="form-control" id="venda_observacao" name="observacao" rows="3" placeholder="Observações sobre a venda..."></textarea>
                        </div>

                        <!-- Informações do Produto Selecionado -->
                        <div class="card mt-3" id="info-produto" style="display: none;">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Informações do Produto</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Descrição:</strong> <span id="info-descricao">-</span></p>
                                        <p><strong>Categoria:</strong> <span id="info-categoria">-</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Estoque Disponível:</strong> <span id="info-estoque">-</span></p>
                                        <p><strong>Preço de Venda:</strong> R$ <span id="info-preco-venda">-</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnSalvarVenda">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                            Registrar Venda
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Rodapé -->
    <div class="text-center mt-4 text-muted">
        <small>Desenvolvido por José Marcos da Silva</small>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ========== NAVEGAÇÃO ADICIONADA ==========
        document.getElementById('nav-vendas').addEventListener('click', function(e) {
            e.preventDefault();
            showSection('vendas');
            setActiveNav('nav-vendas');
            carregarVendas();
            carregarClientesParaVenda();
            carregarProdutosParaVenda();
        });

        // ========== FUNÇÃO SHOWSECTION ATUALIZADA ==========
        function showSection(section) {
            document.getElementById('home-section').style.display = section === 'home' ? 'block' : 'none';
            document.getElementById('pessoas-section').style.display = section === 'pessoas' ? 'block' : 'none';
            document.getElementById('clientes-section').style.display = section === 'clientes' ? 'block' : 'none';
            document.getElementById('agendamentos-section').style.display = section === 'agendamentos' ? 'block' : 'none';
            document.getElementById('produtos-section').style.display = section === 'produtos' ? 'block' : 'none';
            document.getElementById('vendas-section').style.display = section === 'vendas' ? 'block' : 'none';
        }

        // ========== FUNÇÕES DE VENDAS ADICIONADAS ==========

        // Carregar clientes para venda
        function carregarClientesParaVenda() {
            fetch('listar_clientes.php')
                .then(response => response.json())
                .then(data => {
                    const select = document.getElementById('venda_cliente');
                    
                    if (data.success) {
                        select.innerHTML = '<option value="">Selecione um cliente</option>';
                        data.clientes.forEach(cliente => {
                            const option = document.createElement('option');
                            option.value = cliente.id;
                            option.textContent = cliente.nome;
                            select.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                });
        }

        // Carregar produtos para venda
        function carregarProdutosParaVenda() {
            fetch('listar_produtos.php?ativos=1')
                .then(response => response.json())
                .then(data => {
                    const select = document.getElementById('venda_produto');
                    
                    if (data.success) {
                        select.innerHTML = '<option value="">Selecione um produto</option>';
                        data.produtos.forEach(produto => {
                            const option = document.createElement('option');
                            option.value = produto.id;
                            option.textContent = `${produto.nome} (Estoque: ${produto.quantidade_estoque})`;
                            option.setAttribute('data-produto', JSON.stringify(produto));
                            select.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                });
        }

        // Event listener para seleção de produto
        document.getElementById('venda_produto').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const infoProduto = document.getElementById('info-produto');
            
            if (selectedOption.value && selectedOption.getAttribute('data-produto')) {
                const produto = JSON.parse(selectedOption.getAttribute('data-produto'));
                
                // Preencher informações do produto
                document.getElementById('info-descricao').textContent = produto.descricao || 'Sem descrição';
                document.getElementById('info-categoria').textContent = produto.categoria || 'Sem categoria';
                document.getElementById('info-estoque').textContent = produto.quantidade_estoque;
                document.getElementById('info-preco-venda').textContent = parseFloat(produto.preco_venda).toFixed(2);
                
                // Preencher preço unitário automaticamente
                document.getElementById('venda_valor_unitario').value = produto.preco_venda;
                
                // Mostrar informações
                infoProduto.style.display = 'block';
                
                // Calcular valor total
                calcularValorTotalVenda();
            } else {
                infoProduto.style.display = 'none';
                document.getElementById('venda_valor_unitario').value = '';
                document.getElementById('venda_valor_total').value = '';
            }
        });

        // Calcular valor total da venda
        function calcularValorTotalVenda() {
            const quantidade = parseInt(document.getElementById('venda_quantidade').value) || 0;
            const valorUnitario = parseFloat(document.getElementById('venda_valor_unitario').value) || 0;
            const valorTotal = quantidade * valorUnitario;
            
            document.getElementById('venda_valor_total').value = valorTotal.toFixed(2);
        }

        // Event listeners para cálculo automático
        document.getElementById('venda_quantidade').addEventListener('input', calcularValorTotalVenda);
        document.getElementById('venda_valor_unitario').addEventListener('input', calcularValorTotalVenda);

        // Registrar nova venda
        document.getElementById('formNovaVenda').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btnSalvar = document.getElementById('btnSalvarVenda');
            const spinner = btnSalvar.querySelector('.spinner-border');
            const formData = new FormData(this);
            
            // Mostrar loading
            btnSalvar.disabled = true;
            spinner.classList.remove('d-none');
            
            // Enviar dados via AJAX
            fetch('registrar_venda.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('alertNovaVenda').innerHTML = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    
                    // Limpar formulário
                    this.reset();
                    document.getElementById('info-produto').style.display = 'none';
                    
                    // Fechar modal e recarregar vendas após 2 segundos
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('modalNovaVenda')).hide();
                        carregarVendas();
                        carregarDashboard(); // Atualizar dashboard
                    }, 2000);
                } else {
                    document.getElementById('alertNovaVenda').innerHTML = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                document.getElementById('alertNovaVenda').innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Erro ao registrar venda!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            })
            .finally(() => {
                // Esconder loading
                btnSalvar.disabled = false;
                spinner.classList.add('d-none');
            });
        });

        // Carregar vendas
        function carregarVendas() {
            fetch('listar_vendas.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('tabela-vendas');
                    const totalElement = document.getElementById('total-vendas');
                    
                    if (data.success && container) {
                        if (data.vendas && data.vendas.length > 0) {
                            let html = '';
                            data.vendas.forEach(venda => {
                                const dataVenda = new Date(venda.data_venda).toLocaleDateString('pt-BR');
                                const nomeProduto = venda.nome_produto ? venda.nome_produto.replace(/'/g, "\\'").replace(/"/g, '\\"') : 'Produto';
                                const nomeCliente = venda.nome_cliente ? venda.nome_cliente.replace(/'/g, "\\'").replace(/"/g, '\\"') : 'Cliente';
                                
                                html += `
                                    <tr id="venda-${venda.id}">
                                        <td>${dataVenda}</td>
                                        <td>${venda.nome_cliente || 'N/A'}</td>
                                        <td>${venda.nome_produto || 'N/A'}</td>
                                        <td>${venda.quantidade || '0'}</td>
                                        <td>R$ ${parseFloat(venda.valor_total || 0).toFixed(2)}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-info btn-sm" title="Detalhes" onclick="verDetalhesVenda(${venda.id})">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-danger btn-sm" title="Excluir" 
                                                        onclick="confirmarExclusaoVenda(${venda.id}, '${nomeProduto}', '${nomeCliente}')"
                                                        data-venda-id="${venda.id}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                            });
                            container.innerHTML = html;
                            if (totalElement) {
                                totalElement.textContent = data.vendas.length + ' vendas registradas';
                            }
                        } else {
                            container.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Nenhuma venda registrada</td></tr>';
                            if (totalElement) {
                                totalElement.textContent = '0 vendas registradas';
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar vendas:', error);
                    const container = document.getElementById('tabela-vendas');
                    if (container) {
                        container.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Erro ao carregar vendas</td></tr>';
                    }
                });
        }

        // Função para ver detalhes da venda
        function verDetalhesVenda(id) {
            fetch(`detalhes_venda.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const venda = data.venda;
                        let detalhes = `
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">Detalhes da Venda</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>ID:</strong> ${venda.id}</p>
                                            <p><strong>Data:</strong> ${new Date(venda.data_venda).toLocaleDateString('pt-BR')}</p>
                                            <p><strong>Cliente:</strong> ${venda.nome_cliente || 'N/A'}</p>
                                            <p><strong>Produto:</strong> ${venda.nome_produto || 'N/A'}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Quantidade:</strong> ${venda.quantidade}</p>
                                            <p><strong>Valor Unitário:</strong> R$ ${parseFloat(venda.valor_unitario || 0).toFixed(2)}</p>
                                            <p><strong>Valor Total:</strong> R$ ${parseFloat(venda.valor_total || 0).toFixed(2)}</p>
                                            <p><strong>Registrado por:</strong> ${venda.nome_usuario || 'N/A'}</p>
                                        </div>
                                    </div>
                                    ${venda.observacao ? `<hr><p><strong>Observação:</strong> ${venda.observacao}</p>` : ''}
                                </div>
                            </div>
                        `;
                        
                        // Criar modal dinâmico para mostrar detalhes
                        const modalHTML = `
                            <div class="modal fade" id="modalDetalhesVenda" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Detalhes da Venda #${venda.id}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            ${detalhes}
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        // Remover modal anterior se existir
                        const modalAnterior = document.getElementById('modalDetalhesVenda');
                        if (modalAnterior) {
                            modalAnterior.remove();
                        }
                        
                        // Adicionar novo modal ao body
                        document.body.insertAdjacentHTML('beforeend', modalHTML);
                        
                        // Mostrar modal
                        const modal = new bootstrap.Modal(document.getElementById('modalDetalhesVenda'));
                        modal.show();
                    } else {
                        alert('Erro ao carregar detalhes da venda: ' + (data.message || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao carregar detalhes da venda');
                });
        }

        // ========== FUNÇÕES DE EXCLUSÃO DE VENDAS ==========

        // Função para confirmar exclusão de venda
        function confirmarExclusaoVenda(id, produto, cliente) {
            if (confirm(`Tem certeza que deseja excluir a venda do produto "${produto}" para o cliente "${cliente}"?\n\n⚠️ ATENÇÃO: Esta ação não pode ser desfeita!\n✓ O estoque será reestocado automaticamente.`)) {
                excluirVenda(id);
            }
        }

        // Função para excluir venda via AJAX
        function excluirVenda(id) {
            // Encontrar o botão que foi clicado
            const button = event?.target?.closest('button') || document.querySelector(`button[data-venda-id="${id}"]`);
            const originalHTML = button?.innerHTML;
            
            // Mostrar loading no botão
            if (button) {
                button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                button.disabled = true;
                button.classList.add('disabled');
            }
            
            // Mostrar mensagem de processamento
            const row = document.getElementById(`venda-${id}`);
            if (row) {
                row.classList.add('table-warning');
            }
            
            // Enviar requisição para excluir
            fetch(`excluir_venda.php?id=${id}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na rede: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Remover a linha da tabela com efeito
                        if (row) {
                            row.classList.remove('table-warning');
                            row.classList.add('table-danger');
                            setTimeout(() => {
                                row.style.opacity = '0';
                                row.style.transition = 'opacity 0.5s';
                                setTimeout(() => {
                                    row.remove();
                                    // Atualizar contador
                                    const currentCount = parseInt(document.getElementById('total-vendas').textContent) || 0;
                                    document.getElementById('total-vendas').textContent = Math.max(0, currentCount - 1) + ' vendas registradas';
                                }, 500);
                            }, 300);
                        }
                        
                        // Mostrar mensagem de sucesso
                        mostrarAlerta('Venda excluída com sucesso! O estoque foi reestocado.', 'success');
                        
                        // Atualizar dashboard
                        carregarDashboard();
                        carregarProdutos();
                    } else {
                        throw new Error(data.message || 'Erro ao excluir venda');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    
                    // Restaurar botão
                    if (button) {
                        button.innerHTML = originalHTML;
                        button.disabled = false;
                        button.classList.remove('disabled');
                    }
                    
                    // Remover highlight da linha
                    if (row) {
                        row.classList.remove('table-warning', 'table-danger');
                    }
                    
                    // Mostrar mensagem de erro
                    mostrarAlerta('Erro ao excluir venda: ' + error.message, 'danger');
                });
        }

        // Função auxiliar para mostrar alertas
        function mostrarAlerta(mensagem, tipo) {
            // Verificar se já existe um alerta
            let alertaExistente = document.querySelector('.alert-floating');
            if (alertaExistente) {
                alertaExistente.remove();
            }
            
            // Criar novo alerta
            const alerta = document.createElement('div');
            alerta.className = `alert alert-${tipo} alert-floating alert-dismissible fade show`;
            alerta.innerHTML = `
                ${mensagem}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            // Estilos para alerta flutuante
            Object.assign(alerta.style, {
                position: 'fixed',
                top: '20px',
                right: '20px',
                zIndex: '9999',
                minWidth: '300px',
                boxShadow: '0 4px 12px rgba(0,0,0,0.15)'
            });
            
            document.body.appendChild(alerta);
            
            // Remover alerta após 5 segundos
            setTimeout(() => {
                if (alerta.parentNode) {
                    const bsAlert = new bootstrap.Alert(alerta);
                    bsAlert.close();
                }
            }, 5000);
        }

        // ========== SEU CÓDIGO JAVASCRIPT EXISTENTE AQUI ==========
        // Navegação entre seções
        document.getElementById('nav-home').addEventListener('click', function(e) {
            e.preventDefault();
            showSection('home');
            setActiveNav('nav-home');
            carregarDashboard(); // Carrega os dados do dashboard
        });

        document.getElementById('nav-pessoas').addEventListener('click', function(e) {
            e.preventDefault();
            showSection('pessoas');
            setActiveNav('nav-pessoas');
        });

        document.getElementById('nav-clientes').addEventListener('click', function(e) {
            e.preventDefault();
            showSection('clientes');
            setActiveNav('nav-clientes');
            carregarClientesParaAgendamento();
        });

        document.getElementById('nav-agendamentos').addEventListener('click', function(e) {
            e.preventDefault();
            showSection('agendamentos');
            setActiveNav('nav-agendamentos');
            carregarClientesParaAgendamento();
        });

        document.getElementById('nav-produtos').addEventListener('click', function(e) {
            e.preventDefault();
            showSection('produtos');
            setActiveNav('nav-produtos');
            carregarProdutos();
            carregarProdutosParaEntrada();
        });

        function setActiveNav(navId) {
            document.querySelectorAll('.sidebar .nav-link').forEach(nav => {
                nav.classList.remove('active');
            });
            document.getElementById(navId).classList.add('active');
        }

        // ========== DASHBOARD FUNCTIONS ==========

        // Função para carregar dados do dashboard
        function carregarDashboard() {
            carregarEstatisticas();
            carregarAgendamentosHoje();
            carregarUltimosClientes();
            carregarAtividadeRecente();
            carregarGraficoServicos();
        }

        // Carregar estatísticas
        function carregarEstatisticas() {
            fetch('dashboard_estatisticas.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('total-clientes-dash').textContent = data.total_clientes;
                        document.getElementById('total-agendamentos-dash').textContent = data.agendamentos_hoje;
                        document.getElementById('total-servicos-dash').textContent = data.servicos_mes;
                        document.getElementById('faturamento-dash').textContent = 'R$ ' + data.faturamento_mes;
                        document.getElementById('total-produtos-dash').textContent = data.total_produtos || 0;
                        document.getElementById('estoque-baixo-dash').textContent = data.estoque_baixo || 0;
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar estatísticas:', error);
                });
        }

        // Carregar agendamentos de hoje
        function carregarAgendamentosHoje() {
            fetch('dashboard_agendamentos_hoje.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('agendamentos-hoje');
                    
                    if (data.success && data.agendamentos.length > 0) {
                        let html = '';
                        data.agendamentos.forEach(agendamento => {
                            html += `
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                    <div>
                                        <strong>${agendamento.nome_cliente}</strong>
                                        <br>
                                        <small class="text-muted">${agendamento.servico} - ${agendamento.hora_agendamento}</small>
                                    </div>
                                    <span class="badge ${agendamento.status === 'Concluído' ? 'bg-success' : 'bg-warning'}">
                                        ${agendamento.status}
                                    </span>
                                </div>
                            `;
                        });
                        container.innerHTML = html;
                    } else {
                        container.innerHTML = '<p class="text-muted text-center">Nenhum agendamento para hoje</p>';
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar agendamentos:', error);
                    document.getElementById('agendamentos-hoje').innerHTML = '<p class="text-danger">Erro ao carregar agendamentos</p>';
                });
        }

        // Carregar últimos clientes
        function carregarUltimosClientes() {
            fetch('dashboard_ultimos_clientes.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('ultimos-clientes');
                    
                    if (data.success && data.clientes.length > 0) {
                        let html = '';
                        data.clientes.forEach(cliente => {
                            const dataCadastro = formatarData(cliente.data_cadastro);
                            html += `
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                    <div>
                                        <strong>${cliente.nome}</strong>
                                        <br>
                                        <small class="text-muted">Cadastro: ${dataCadastro}</small>
                                    </div>
                                    <span class="badge bg-success">Novo</span>
                                </div>
                            `;
                        });
                        container.innerHTML = html;
                    } else {
                        container.innerHTML = '<p class="text-muted text-center">Nenhum cliente cadastrado</p>';
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar clientes:', error);
                    document.getElementById('ultimos-clientes').innerHTML = '<p class="text-danger">Erro ao carregar clientes</p>';
                });
        }

        // Carregar atividade recente
        function carregarAtividadeRecente() {
            fetch('dashboard_atividade.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('atividade-recente');
                    
                    if (data.success && data.atividades.length > 0) {
                        let html = '';
                        data.atividades.forEach(atividade => {
                            html += `
                                <div class="d-flex align-items-start border-bottom pb-2 mb-2">
                                    <i class="bi ${atividade.icone} me-2 mt-1 text-${atividade.cor}"></i>
                                    <div class="flex-grow-1">
                                        <strong>${atividade.titulo}</strong>
                                        <br>
                                        <small class="text-muted">${atividade.descricao}</small>
                                        <br>
                                        <small class="text-muted">${atividade.tempo}</small>
                                    </div>
                                </div>
                            `;
                        });
                        container.innerHTML = html;
                    } else {
                        container.innerHTML = '<p class="text-muted text-center">Nenhuma atividade recente</p>';
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar atividades:', error);
                    document.getElementById('atividade-recente').innerHTML = '<p class="text-danger">Erro ao carregar atividades</p>';
                });
        }

        // Carregar gráfico de serviços
        function carregarGraficoServicos() {
            fetch('dashboard_servicos.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('grafico-servicos');
                    
                    if (data.success && data.servicos.length > 0) {
                        let html = '<div class="table-responsive"><table class="table table-sm">';
                        html += '<thead><tr><th>Serviço</th><th>Quantidade</th><th width="40%"></th></tr></thead><tbody>';
                        
                        data.servicos.forEach(servico => {
                            const porcentagem = (servico.quantidade / data.total) * 100;
                            html += `
                                <tr>
                                    <td>${servico.servico}</td>
                                    <td>${servico.quantidade}</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: ${porcentagem}%;" 
                                                 aria-valuenow="${porcentagem}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                ${porcentagem.toFixed(1)}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                        
                        html += '</tbody></table></div>';
                        container.innerHTML = html;
                    } else {
                        container.innerHTML = '<p class="text-muted text-center">Nenhum serviço registrado</p>';
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar serviços:', error);
                    document.getElementById('grafico-servicos').innerHTML = '<p class="text-danger">Erro ao carregar gráfico</p>';
                });
        }

        // ========== USER MANAGEMENT FUNCTIONS ==========

        // Função para editar usuário
        function editarUsuario(id) {
            const modal = new bootstrap.Modal(document.getElementById('modalEditarUsuario'));
            const btnSalvar = document.getElementById('btnSalvarUsuario');
            const spinner = btnSalvar.querySelector('.spinner-border');
            
            // Limpar alertas e formulário
            document.getElementById('alertEditarUsuario').innerHTML = '';
            document.getElementById('edit_usuario_senha').value = '';
            
            // Mostrar loading
            btnSalvar.disabled = true;
            spinner.classList.remove('d-none');
            
            // Buscar dados do usuário
            fetch(`editar_usuario.php?id=${id}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na rede: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const usuario = data.usuario;
                        
                        // Preencher formulário com dados atuais
                        document.getElementById('edit_usuario_id').value = usuario.id;
                        document.getElementById('edit_usuario_nome').value = usuario.nome;
                        document.getElementById('edit_usuario_email').value = usuario.email;
                        document.getElementById('edit_usuario_cpf').value = usuario.cpf || 'Não informado';
                        document.getElementById('edit_usuario_nivel').value = usuario.nivel;
                        document.getElementById('edit_usuario_data').value = formatarData(usuario.data_cadastro);
                        
                        // Atualizar título do modal
                        document.getElementById('modalEditarUsuarioLabel').textContent = `Editar Usuário: ${usuario.nome}`;
                        
                        // Esconder loading
                        btnSalvar.disabled = false;
                        spinner.classList.add('d-none');
                        
                        // Mostrar modal
                        modal.show();
                    } else {
                        mostrarAlertaEditarUsuario(data.message, 'danger');
                        btnSalvar.disabled = false;
                        spinner.classList.add('d-none');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    mostrarAlertaEditarUsuario('Erro ao carregar dados do usuário: ' + error.message, 'danger');
                    btnSalvar.disabled = false;
                    spinner.classList.add('d-none');
                });
        }

        // Função para mostrar alertas no modal de editar usuário
        function mostrarAlertaEditarUsuario(mensagem, tipo) {
            const alertDiv = document.getElementById('alertEditarUsuario');
            alertDiv.innerHTML = `
                <div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
                    ${mensagem}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        }

        // Event listener para o formulário de edição de usuário
        document.getElementById('formEditarUsuario').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btnSalvar = document.getElementById('btnSalvarUsuario');
            const spinner = btnSalvar.querySelector('.spinner-border');
            const formData = new FormData(this);
            
            // Validar senha se for preenchida
            const senha = formData.get('senha');
            if (senha && senha.length < 6) {
                mostrarAlertaEditarUsuario('A senha deve ter no mínimo 6 caracteres!', 'danger');
                return;
            }
            
            // Mostrar loading
            btnSalvar.disabled = true;
            spinner.classList.remove('d-none');
            
            // Enviar dados via AJAX
            fetch('editar_usuario.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na rede: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    mostrarAlertaEditarUsuario(data.message, 'success');
                    
                    // Fechar modal e recarregar a página após 2 segundos
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarUsuario'));
                        modal.hide();
                        // Recarregar a página para atualizar a tabela
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    }, 2000);
                } else {
                    mostrarAlertaEditarUsuario(data.message, 'danger');
                    btnSalvar.disabled = false;
                    spinner.classList.add('d-none');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarAlertaEditarUsuario('Erro ao atualizar usuário: ' + error.message, 'danger');
                btnSalvar.disabled = false;
                spinner.classList.add('d-none');
            });
        });

        // Função para ver informações do usuário
        function verInformacoes(id) {
            const modal = new bootstrap.Modal(document.getElementById('modalInformacoes'));
            document.getElementById('informacoesUsuario').innerHTML = `
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="mt-2">Carregando informações...</p>
                </div>
            `;
            
            // Buscar dados reais do usuário
            fetch(`informacoes_usuario.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const usuario = data.usuario;
                        document.getElementById('informacoesUsuario').innerHTML = `
                            <p><strong>ID:</strong> ${usuario.id}</p>
                            <p><strong>Nome:</strong> ${usuario.nome}</p>
                            <p><strong>Email:</strong> ${usuario.email}</p>
                            <p><strong>CPF:</strong> ${usuario.cpf || 'Não informado'}</p>
                            <p><strong>Senha:</strong> <span class="badge bg-secondary">${usuario.senha}</span></p>
                            <p><strong>Nível:</strong> <span class="badge ${usuario.nivel == 'Administrador' ? 'bg-primary' : 'bg-info'}">${usuario.nivel}</span></p>
                            <p><strong>Data de Cadastro:</strong> ${formatarData(usuario.data_cadastro)}</p>
                            <p><strong>Status:</strong> <span class="badge bg-success">Ativo</span></p>
                        `;
                    } else {
                        document.getElementById('informacoesUsuario').innerHTML = `
                            <div class="alert alert-danger">
                                ${data.message}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    document.getElementById('informacoesUsuario').innerHTML = `
                        <div class="alert alert-danger">
                            Erro ao carregar informações do usuário!
                        </div>
                    `;
                });
            
            modal.show();
        }

        // Função para confirmar exclusão
        function confirmarExclusao(id, nome) {
            if (confirm(`Tem certeza que deseja excluir o usuário "${nome}"?\n\nEsta ação não pode ser desfeita!`)) {
                window.location.href = `painel.php?excluir_id=${id}`;
            }
        }

        // Função para formatar data
        function formatarData(data) {
            if (!data) return 'N/A';
            const date = new Date(data);
            return date.toLocaleDateString('pt-BR');
        }

        // Função para mostrar alertas no modal de perfil
        function mostrarAlertaPerfil(mensagem, tipo) {
            const alertDiv = document.getElementById('alertPerfil');
            alertDiv.innerHTML = `
                <div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
                    ${mensagem}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        }

        // Event listener para o formulário de edição de perfil
        document.getElementById('formEditarPerfil').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btnSalvar = document.getElementById('btnSalvarPerfil');
            const spinner = btnSalvar.querySelector('.spinner-border');
            const formData = new FormData(this);
            
            // Mostrar loading
            btnSalvar.disabled = true;
            spinner.classList.remove('d-none');
            
            // Enviar dados via AJAX
            fetch('editar_perfil.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlertaPerfil(data.message, 'success');
                    
                    // Atualizar dados na interface
                    document.querySelector('.navbar-text span').textContent = data.nome;
                    document.querySelector('.sidebar h6.text-white').textContent = 'JK BARBERSHOP';
                    
                    // Fechar modal após 2 segundos
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('modalEditarPerfil')).hide();
                    }, 2000);
                } else {
                    mostrarAlertaPerfil(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarAlertaPerfil('Erro ao atualizar perfil!', 'danger');
            })
            .finally(() => {
                // Esconder loading
                btnSalvar.disabled = false;
                spinner.classList.add('d-none');
            });
        });

        // Função para abrir modal de edição de perfil
        function abrirEditarPerfil() {
            const modal = new bootstrap.Modal(document.getElementById('modalEditarPerfil'));
            const btnSalvar = document.getElementById('btnSalvarPerfil');
            const spinner = btnSalvar.querySelector('.spinner-border');
            
            // Limpar alertas
            document.getElementById('alertPerfil').innerHTML = '';
            
            // Mostrar loading
            btnSalvar.disabled = true;
            spinner.classList.remove('d-none');
            
            // Buscar dados do usuário
            fetch('editar_perfil.php')
                .then(response => response.json())
                .then(data => {
                    // Preencher formulário com dados atuais
                    document.getElementById('edit_nome').value = data.nome;
                    document.getElementById('edit_email').value = data.email;
                    document.getElementById('edit_nivel').value = data.nivel;
                    document.getElementById('edit_data').value = formatarData(data.data_cadastro);
                    
                    // Esconder loading
                    btnSalvar.disabled = false;
                    spinner.classList.add('d-none');
                    
                    // Mostrar modal
                    modal.show();
                })
                .catch(error => {
                    console.error('Erro:', error);
                    mostrarAlertaPerfil('Erro ao carregar dados do perfil!', 'danger');
                    btnSalvar.disabled = false;
                    spinner.classList.add('d-none');
                });
        }

        // ========== CLIENTES FUNCTIONS ==========

        // Função para editar cliente
        function editarCliente(id) {
            const modal = new bootstrap.Modal(document.getElementById('modalEditarCliente'));
            const btnSalvar = document.getElementById('btnSalvarCliente');
            const spinner = btnSalvar.querySelector('.spinner-border');
            
            // Limpar alertas
            document.getElementById('alertEditarCliente').innerHTML = '';
            
            // Mostrar loading
            btnSalvar.disabled = true;
            spinner.classList.remove('d-none');
            
            // Buscar dados do cliente
            fetch(`editar_cliente.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const cliente = data.cliente;
                        
                        // Preencher formulário com dados atuais
                        document.getElementById('edit_cliente_id').value = cliente.id;
                        document.getElementById('edit_cliente_nome').value = cliente.nome;
                        document.getElementById('edit_cliente_telefone').value = cliente.telefone || '';
                        document.getElementById('edit_cliente_email').value = cliente.email || '';
                        document.getElementById('edit_cliente_nascimento').value = cliente.data_nascimento || '';
                        document.getElementById('edit_cliente_observacao').value = cliente.observacao || '';
                        
                        // Atualizar título do modal
                        document.getElementById('modalEditarClienteLabel').textContent = `Editar Cliente: ${cliente.nome}`;
                        
                        // Esconder loading
                        btnSalvar.disabled = false;
                        spinner.classList.add('d-none');
                        
                        // Mostrar modal
                        modal.show();
                    } else {
                        alert(data.message);
                        btnSalvar.disabled = false;
                        spinner.classList.add('d-none');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao carregar dados do cliente!');
                    btnSalvar.disabled = false;
                    spinner.classList.add('d-none');
                });
        }

        // Função para ver informações do cliente
        function verInformacoesCliente(id) {
            const modal = new bootstrap.Modal(document.getElementById('modalInformacoesCliente'));
            document.getElementById('informacoesCliente').innerHTML = `
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="mt-2">Carregando informações...</p>
                </div>
            `;
            
            // Buscar dados reais do cliente
            fetch(`informacoes_cliente.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const cliente = data.cliente;
                        let html = `
                            <h6>Dados Pessoais</h6>
                            <p><strong>Nome:</strong> ${cliente.nome}</p>
                            <p><strong>Telefone:</strong> ${cliente.telefone || 'Não informado'}</p>
                            <p><strong>Email:</strong> ${cliente.email || 'Não informado'}</p>
                            <p><strong>Data de Nascimento:</strong> ${cliente.data_nascimento ? formatarData(cliente.data_nascimento) : 'Não informada'}</p>
                            <p><strong>Data de Cadastro:</strong> ${formatarData(cliente.data_cadastro)}</p>
                            <p><strong>Observação:</strong> ${cliente.observacao || 'Nenhuma'}</p>
                        `;
                        
                        if (data.agendamentos && data.agendamentos.length > 0) {
                            html += `<hr><h6>Histórico de Agendamentos</h6>`;
                            html += `<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Data</th><th>Serviço</th><th>Valor</th><th>Status</th></tr></thead><tbody>`;
                            
                            data.agendamentos.forEach(agendamento => {
                                html += `
                                    <tr>
                                        <td>${formatarData(agendamento.data_agendamento)} ${agendamento.hora_agendamento}</td>
                                        <td>${agendamento.servico}</td>
                                        <td>R$ ${agendamento.valor || '0.00'}</td>
                                        <td><span class="badge ${agendamento.status === 'Concluído' ? 'bg-success' : agendamento.status === 'Cancelado' ? 'bg-danger' : 'bg-warning'}">${agendamento.status}</span></td>
                                    </tr>
                                `;
                            });
                            
                            html += `</tbody></table></div>`;
                        } else {
                            html += `<hr><p class="text-muted">Nenhum agendamento encontrado para este cliente.</p>`;
                        }
                        
                        document.getElementById('informacoesCliente').innerHTML = html;
                    } else {
                        document.getElementById('informacoesCliente').innerHTML = `
                            <div class="alert alert-danger">
                                ${data.message}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    document.getElementById('informacoesCliente').innerHTML = `
                        <div class="alert alert-danger">
                            Erro ao carregar informações do cliente!
                        </div>
                    `;
                });
            
            modal.show();
        }

        // Função para confirmar exclusão de cliente
        function confirmarExclusaoCliente(id, nome) {
            if (confirm(`Tem certeza que deseja excluir o cliente "${nome}"?\n\nEsta ação não pode ser desfeita!`)) {
                // Mostrar loading
                const button = event.target;
                const originalHTML = button.innerHTML;
                button.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Carregando...</span></div>';
                button.disabled = true;
                
                fetch(`excluir_cliente.php?id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            window.location.reload();
                        } else {
                            alert(data.message);
                            button.innerHTML = originalHTML;
                            button.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao excluir cliente!');
                        button.innerHTML = originalHTML;
                        button.disabled = false;
                    });
            }
        }

        // Event listener para o formulário de edição de cliente
        document.getElementById('formEditarCliente').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btnSalvar = document.getElementById('btnSalvarCliente');
            const spinner = btnSalvar.querySelector('.spinner-border');
            const formData = new FormData(this);
            
            // Mostrar loading
            btnSalvar.disabled = true;
            spinner.classList.remove('d-none');
            
            // Enviar dados via AJAX
            fetch('editar_cliente.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('alertEditarCliente').innerHTML = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    
                    // Fechar modal e recarregar a página após 2 segundos
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('modalEditarCliente')).hide();
                        window.location.reload();
                    }, 2000);
                } else {
                    document.getElementById('alertEditarCliente').innerHTML = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    btnSalvar.disabled = false;
                    spinner.classList.add('d-none');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                document.getElementById('alertEditarCliente').innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Erro ao atualizar cliente!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                btnSalvar.disabled = false;
                spinner.classList.add('d-none');
            });
        });

        // ========== PRODUTOS FUNCTIONS ==========

        // Carregar produtos
        function carregarProdutos() {
            fetch('listar_produtos.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('tabela-produtos');
                    const totalElement = document.getElementById('total-produtos');
                    
                    if (data.success && data.produtos.length > 0) {
                        let html = '';
                        data.produtos.forEach(produto => {
                            const estoqueClass = produto.quantidade_estoque <= produto.quantidade_minima ? 'text-danger fw-bold' : '';
                            const statusBadge = produto.status === 'Ativo' ? 'bg-success' : 'bg-secondary';
                            
                            html += `
                                <tr>
                                    <td>${produto.nome}</td>
                                    <td>${produto.categoria || 'Sem categoria'}</td>
                                    <td>R$ ${parseFloat(produto.preco_venda).toFixed(2)}</td>
                                    <td class="${estoqueClass}">${produto.quantidade_estoque}</td>
                                    <td><span class="badge ${statusBadge}">${produto.status}</span></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" title="Editar" onclick="editarProduto(${produto.id})">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-info" title="Informações" onclick="verInformacoesProduto(${produto.id})">
                                                <i class="bi bi-info-circle"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" title="Excluir" onclick="confirmarExclusaoProduto(${produto.id}, '${produto.nome.replace(/'/g, "\\'")}')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                        container.innerHTML = html;
                        totalElement.textContent = data.produtos.length + ' registros';
                    } else {
                        container.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Nenhum produto cadastrado</td></tr>';
                        totalElement.textContent = '0 registros';
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    document.getElementById('tabela-produtos').innerHTML = '<tr><td colspan="6" class="text-center text-danger">Erro ao carregar produtos</td></tr>';
                });
        }

        // Carregar produtos para entrada de estoque
        function carregarProdutosParaEntrada() {
            fetch('listar_produtos.php?ativos=1')
                .then(response => response.json())
                .then(data => {
                    const select = document.getElementById('entrada_produto');
                    
                    if (data.success) {
                        select.innerHTML = '<option value="">Selecione um produto</option>';
                        data.produtos.forEach(produto => {
                            const option = document.createElement('option');
                            option.value = produto.id;
                            option.textContent = `${produto.nome} (Estoque: ${produto.quantidade_estoque})`;
                            select.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                });
        }

        // Cadastrar novo produto
        document.getElementById('formNovoProduto').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btnSalvar = document.getElementById('btnSalvarProduto');
            const spinner = btnSalvar.querySelector('.spinner-border');
            const formData = new FormData(this);
            
            // Mostrar loading
            btnSalvar.disabled = true;
            spinner.classList.remove('d-none');
            
            // Enviar dados via AJAX
            fetch('cadastrar_produto.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('alertNovoProduto').innerHTML = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    
                    // Limpar formulário
                    this.reset();
                    
                    // Fechar modal e recarregar produtos após 2 segundos
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('modalNovoProduto')).hide();
                        carregarProdutos();
                        carregarDashboard(); // Atualizar dashboard
                    }, 2000);
                } else {
                    document.getElementById('alertNovoProduto').innerHTML = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                document.getElementById('alertNovoProduto').innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Erro ao cadastrar produto!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            })
            .finally(() => {
                // Esconder loading
                btnSalvar.disabled = false;
                spinner.classList.add('d-none');
            });
        });

        // Editar produto
        function editarProduto(id) {
            const modal = new bootstrap.Modal(document.getElementById('modalEditarProduto'));
            const btnSalvar = document.getElementById('btnSalvarEditarProduto');
            const spinner = btnSalvar.querySelector('.spinner-border');
            
            // Limpar alertas
            document.getElementById('alertEditarProduto').innerHTML = '';
            
            // Mostrar loading
            btnSalvar.disabled = true;
            spinner.classList.remove('d-none');
            
            // Buscar dados do produto
            fetch(`editar_produto.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const produto = data.produto;
                        
                        // Preencher formulário com dados atuais
                        document.getElementById('edit_produto_id').value = produto.id;
                        document.getElementById('edit_produto_nome').value = produto.nome;
                        document.getElementById('edit_produto_descricao').value = produto.descricao || '';
                        document.getElementById('edit_produto_categoria').value = produto.categoria || '';
                        document.getElementById('edit_produto_preco_custo').value = produto.preco_custo || '';
                        document.getElementById('edit_produto_preco_venda').value = produto.preco_venda;
                        document.getElementById('edit_produto_quantidade_minima').value = produto.quantidade_minima;
                        document.getElementById('edit_produto_codigo_barras').value = produto.codigo_barras || '';
                        document.getElementById('edit_produto_fornecedor').value = produto.fornecedor || '';
                        document.getElementById('edit_produto_status').value = produto.status;
                        
                        // Atualizar título do modal
                        document.getElementById('modalEditarProdutoLabel').textContent = `Editar Produto: ${produto.nome}`;
                        
                        // Esconder loading
                        btnSalvar.disabled = false;
                        spinner.classList.add('d-none');
                        
                        // Mostrar modal
                        modal.show();
                    } else {
                        alert(data.message);
                        btnSalvar.disabled = false;
                        spinner.classList.add('d-none');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao carregar dados do produto!');
                    btnSalvar.disabled = false;
                    spinner.classList.add('d-none');
                });
        }

        // Event listener para edição de produto
        document.getElementById('formEditarProduto').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btnSalvar = document.getElementById('btnSalvarEditarProduto');
            const spinner = btnSalvar.querySelector('.spinner-border');
            const formData = new FormData(this);
            
            // Mostrar loading
            btnSalvar.disabled = true;
            spinner.classList.remove('d-none');
            
            // Enviar dados via AJAX
            fetch('editar_produto.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('alertEditarProduto').innerHTML = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    
                    // Fechar modal e recarregar produtos após 2 segundos
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('modalEditarProduto')).hide();
                        carregarProdutos();
                        carregarDashboard(); // Atualizar dashboard
                    }, 2000);
                } else {
                    document.getElementById('alertEditarProduto').innerHTML = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    btnSalvar.disabled = false;
                    spinner.classList.add('d-none');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                document.getElementById('alertEditarProduto').innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Erro ao atualizar produto!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                btnSalvar.disabled = false;
                spinner.classList.add('d-none');
            });
        });

        // Ver informações do produto
        function verInformacoesProduto(id) {
            const modal = new bootstrap.Modal(document.getElementById('modalInformacoesProduto'));
            document.getElementById('informacoesProduto').innerHTML = `
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="mt-2">Carregando informações...</p>
                </div>
            `;
            
            // Buscar dados reais do produto
            fetch(`informacoes_produto.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const produto = data.produto;
                        const estoqueClass = produto.quantidade_estoque <= produto.quantidade_minima ? 'text-danger fw-bold' : '';
                        const statusBadge = produto.status === 'Ativo' ? 'bg-success' : 'bg-secondary';
                        
                        document.getElementById('informacoesProduto').innerHTML = `
                            <p><strong>Nome:</strong> ${produto.nome}</p>
                            <p><strong>Descrição:</strong> ${produto.descricao || 'Nenhuma'}</p>
                            <p><strong>Categoria:</strong> ${produto.categoria || 'Sem categoria'}</p>
                            <p><strong>Preço de Custo:</strong> R$ ${produto.preco_custo ? parseFloat(produto.preco_custo).toFixed(2) : '0.00'}</p>
                            <p><strong>Preço de Venda:</strong> R$ ${parseFloat(produto.preco_venda).toFixed(2)}</p>
                            <p><strong>Estoque:</strong> <span class="${estoqueClass}">${produto.quantidade_estoque} unidades</span></p>
                            <p><strong>Estoque Mínimo:</strong> ${produto.quantidade_minima} unidades</p>
                            <p><strong>Código de Barras:</strong> ${produto.codigo_barras || 'Não informado'}</p>
                            <p><strong>Fornecedor:</strong> ${produto.fornecedor || 'Não informado'}</p>
                            <p><strong>Status:</strong> <span class="badge ${statusBadge}">${produto.status}</span></p>
                            <p><strong>Data de Cadastro:</strong> ${formatarData(produto.data_cadastro)}</p>
                            <p><strong>Última Atualização:</strong> ${formatarData(produto.data_atualizacao)}</p>
                        `;
                    } else {
                        document.getElementById('informacoesProduto').innerHTML = `
                            <div class="alert alert-danger">
                                ${data.message}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    document.getElementById('informacoesProduto').innerHTML = `
                        <div class="alert alert-danger">
                            Erro ao carregar informações do produto!
                        </div>
                    `;
                });
            
            modal.show();
        }

        // Confirmar exclusão de produto
        function confirmarExclusaoProduto(id, nome) {
            if (confirm(`Tem certeza que deseja excluir o produto "${nome}"?\n\nEsta ação não pode ser desfeita!`)) {
                // Mostrar loading
                const button = event.target;
                const originalHTML = button.innerHTML;
                button.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Carregando...</span></div>';
                button.disabled = true;
                
                fetch(`excluir_produto.php?id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            carregarProdutos();
                            carregarDashboard(); // Atualizar dashboard
                        } else {
                            alert(data.message);
                            button.innerHTML = originalHTML;
                            button.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao excluir produto!');
                        button.innerHTML = originalHTML;
                        button.disabled = false;
                    });
            }
        }

        // Entrada de estoque
        document.getElementById('formEntradaEstoque').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btnSalvar = document.getElementById('btnSalvarEntradaEstoque');
            const spinner = btnSalvar.querySelector('.spinner-border');
            const formData = new FormData(this);
            
            // Mostrar loading
            btnSalvar.disabled = true;
            spinner.classList.remove('d-none');
            
            // Enviar dados via AJAX
            fetch('entrada_estoque.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('alertEntradaEstoque').innerHTML = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    
                    // Limpar formulário
                    this.reset();
                    
                    // Fechar modal e recarregar produtos após 2 segundos
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('modalEntradaEstoque')).hide();
                        carregarProdutos();
                        carregarProdutosParaEntrada();
                        carregarDashboard(); // Atualizar dashboard
                    }, 2000);
                } else {
                    document.getElementById('alertEntradaEstoque').innerHTML = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                document.getElementById('alertEntradaEstoque').innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Erro ao registrar entrada no estoque!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            })
            .finally(() => {
                // Esconder loading
                btnSalvar.disabled = false;
                spinner.classList.add('d-none');
            });
        });

        // ========== AGENDAMENTOS FUNCTIONS ==========

        // Função para carregar clientes no select de agendamentos
        function carregarClientesParaAgendamento() {
            fetch('listar_clientes.php')
                .then(response => response.json())
                .then(data => {
                    const selectNovo = document.getElementById('agendamento_cliente');
                    const selectEditar = document.getElementById('edit_agendamento_cliente');
                    
                    if (data.success) {
                        // Limpar selects
                        selectNovo.innerHTML = '<option value="">Selecione um cliente</option>';
                        selectEditar.innerHTML = '<option value="">Selecione um cliente</option>';
                        
                        data.clientes.forEach(cliente => {
                            const optionNovo = document.createElement('option');
                            optionNovo.value = cliente.id;
                            optionNovo.textContent = cliente.nome;
                            selectNovo.appendChild(optionNovo);
                            
                            const optionEditar = document.createElement('option');
                            optionEditar.value = cliente.id;
                            optionEditar.textContent = cliente.nome;
                            selectEditar.appendChild(optionEditar);
                        });
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                });
        }

        // Função para editar agendamento
        function editarAgendamento(id) {
            const modal = new bootstrap.Modal(document.getElementById('modalEditarAgendamento'));
            const btnSalvar = document.getElementById('btnSalvarAgendamento');
            const spinner = btnSalvar.querySelector('.spinner-border');
            
            // Limpar alertas
            document.getElementById('alertEditarAgendamento').innerHTML = '';
            
            // Mostrar loading
            btnSalvar.disabled = true;
            spinner.classList.remove('d-none');
            
            // Buscar dados do agendamento
            fetch(`editar_agendamento.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const agendamento = data.agendamento;
                        
                        // Preencher formulário com dados atuais
                        document.getElementById('edit_agendamento_id').value = agendamento.id;
                        document.getElementById('edit_agendamento_cliente').value = agendamento.cliente_id;
                        document.getElementById('edit_agendamento_data').value = agendamento.data_agendamento;
                        document.getElementById('edit_agendamento_hora').value = agendamento.hora_agendamento;
                        document.getElementById('edit_agendamento_servico').value = agendamento.servico;
                        document.getElementById('edit_agendamento_tecnico').value = agendamento.tecnico || '';
                        document.getElementById('edit_agendamento_valor').value = agendamento.valor || '';
                        document.getElementById('edit_agendamento_status').value = agendamento.status;
                        document.getElementById('edit_agendamento_observacao').value = agendamento.observacao || '';
                        
                        // Atualizar título do modal
                        document.getElementById('modalEditarAgendamentoLabel').textContent = `Editar Agendamento: ${agendamento.servico}`;
                        
                        // Esconder loading
                        btnSalvar.disabled = false;
                        spinner.classList.add('d-none');
                        
                        // Mostrar modal
                        modal.show();
                    } else {
                        alert(data.message);
                        btnSalvar.disabled = false;
                        spinner.classList.add('d-none');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao carregar dados do agendamento!');
                    btnSalvar.disabled = false;
                    spinner.classList.add('d-none');
                });
        }

        // Função para ver informações do agendamento
        function verInformacoesAgendamento(id) {
            const modal = new bootstrap.Modal(document.getElementById('modalInformacoesAgendamento'));
            document.getElementById('informacoesAgendamento').innerHTML = `
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="mt-2">Carregando informações...</p>
                </div>
            `;
            
            // Buscar dados reais do agendamento
            fetch(`informacoes_agendamento.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const agendamento = data.agendamento;
                        document.getElementById('informacoesAgendamento').innerHTML = `
                            <p><strong>Cliente:</strong> ${agendamento.nome_cliente}</p>
                            <p><strong>Telefone:</strong> ${agendamento.telefone || 'Não informado'}</p>
                            <p><strong>Email:</strong> ${agendamento.email || 'Não informado'}</p>
                            <p><strong>Data:</strong> ${formatarData(agendamento.data_agendamento)}</p>
                            <p><strong>Hora:</strong> ${agendamento.hora_agendamento}</p>
                            <p><strong>Serviço:</strong> ${agendamento.servico}</p>
                            <p><strong>Técnico:</strong> ${agendamento.tecnico || 'Não informado'}</p>
                            <p><strong>Valor:</strong> R$ ${agendamento.valor || '0.00'}</p>
                            <p><strong>Status:</strong> <span class="badge ${agendamento.status === 'Concluído' ? 'bg-success' : agendamento.status === 'Cancelado' ? 'bg-danger' : 'bg-warning'}">${agendamento.status}</span></p>
                            <p><strong>Observação:</strong> ${agendamento.observacao || 'Nenhuma'}</p>
                        `;
                    } else {
                        document.getElementById('informacoesAgendamento').innerHTML = `
                            <div class="alert alert-danger">
                                ${data.message}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    document.getElementById('informacoesAgendamento').innerHTML = `
                        <div class="alert alert-danger">
                            Erro ao carregar informações do agendamento!
                        </div>
                    `;
                });
            
            modal.show();
        }

        // Função para confirmar exclusão de agendamento
        function confirmarExclusaoAgendamento(id, servico) {
            if (confirm(`Tem certeza que deseja excluir o agendamento "${servico}"?\n\nEsta ação não pode ser desfeita!`)) {
                // Mostrar loading
                const button = event.target;
                const originalHTML = button.innerHTML;
                button.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Carregando...</span></div>';
                button.disabled = true;
                
                fetch(`excluir_agendamento.php?id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            window.location.reload();
                        } else {
                            alert(data.message);
                            button.innerHTML = originalHTML;
                            button.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao excluir agendamento!');
                        button.innerHTML = originalHTML;
                        button.disabled = false;
                    });
            }
        }

        // Event listener para o formulário de edição de agendamento
        document.getElementById('formEditarAgendamento').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btnSalvar = document.getElementById('btnSalvarAgendamento');
            const spinner = btnSalvar.querySelector('.spinner-border');
            const formData = new FormData(this);
            
            // Mostrar loading
            btnSalvar.disabled = true;
            spinner.classList.remove('d-none');
            
            // Enviar dados via AJAX
            fetch('editar_agendamento.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('alertEditarAgendamento').innerHTML = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    
                    // Fechar modal e recarregar a página após 2 segundos
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('modalEditarAgendamento')).hide();
                        window.location.reload();
                    }, 2000);
                } else {
                    document.getElementById('alertEditarAgendamento').innerHTML = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    btnSalvar.disabled = false;
                    spinner.classList.add('d-none');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                document.getElementById('alertEditarAgendamento').innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Erro ao atualizar agendamento!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                btnSalvar.disabled = false;
                spinner.classList.add('d-none');
            });
        });

        // Inicializar com a seção home visível
        showSection('home');
        setActiveNav('nav-home');
        carregarDashboard(); // Carrega o dashboard automaticamente
        carregarClientesParaAgendamento(); // Carrega clientes para os selects

        // Fechar alertas automaticamente após 5 segundos
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>