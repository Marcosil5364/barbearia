<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receber e sanitizar os dados
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);
    $categoria = filter_input(INPUT_POST, 'categoria', FILTER_SANITIZE_SPECIAL_CHARS);
    $preco_custo = filter_input(INPUT_POST, 'preco_custo', FILTER_VALIDATE_FLOAT);
    $preco_venda = filter_input(INPUT_POST, 'preco_venda', FILTER_VALIDATE_FLOAT);
    $quantidade_estoque = filter_input(INPUT_POST, 'quantidade_estoque', FILTER_VALIDATE_INT);
    $quantidade_minima = filter_input(INPUT_POST, 'quantidade_minima', FILTER_VALIDATE_INT);
    $codigo_barras = filter_input(INPUT_POST, 'codigo_barras', FILTER_SANITIZE_SPECIAL_CHARS);
    $fornecedor = filter_input(INPUT_POST, 'fornecedor', FILTER_SANITIZE_SPECIAL_CHARS);

    // Validações
    if (empty($nome)) {
        echo json_encode(['success' => false, 'message' => 'Nome do produto é obrigatório!']);
        exit;
    }

    if ($preco_venda === false || $preco_venda <= 0) {
        echo json_encode(['success' => false, 'message' => 'Preço de venda deve ser um valor positivo!']);
        exit;
    }

    // Valores padrão
    $preco_custo = $preco_custo ?: 0;
    $quantidade_estoque = $quantidade_estoque ?: 0;
    $quantidade_minima = $quantidade_minima ?: 0;

    try {
        // Verificar se a tabela produtos existe
        $tabela_existe = $pdo->query("SHOW TABLES LIKE 'produtos'")->rowCount() > 0;
        
        if (!$tabela_existe) {
            // Criar tabela se não existir
            $pdo->exec("CREATE TABLE produtos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(255) NOT NULL,
                descricao TEXT,
                categoria VARCHAR(100),
                preco_custo DECIMAL(10,2) DEFAULT 0,
                preco_venda DECIMAL(10,2) NOT NULL,
                quantidade_estoque INT DEFAULT 0,
                quantidade_minima INT DEFAULT 0,
                codigo_barras VARCHAR(100),
                fornecedor VARCHAR(255),
                data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                status ENUM('Ativo','Inativo') DEFAULT 'Ativo'
            )");
        }

        // Inserir produto
        $query = $pdo->prepare("INSERT INTO produtos 
            (nome, descricao, categoria, preco_custo, preco_venda, quantidade_estoque, quantidade_minima, codigo_barras, fornecedor) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $sucesso = $query->execute([
            $nome, 
            $descricao, 
            $categoria, 
            $preco_custo, 
            $preco_venda, 
            $quantidade_estoque, 
            $quantidade_minima, 
            $codigo_barras, 
            $fornecedor
        ]);

        if ($sucesso) {
            echo json_encode([
                'success' => true, 
                'message' => 'Produto cadastrado com sucesso!'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Erro ao cadastrar produto no banco de dados!'
            ]);
        }

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Erro no banco de dados: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Método não permitido!'
    ]);
}
?>