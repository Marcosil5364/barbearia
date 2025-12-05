-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 05/12/2025 às 14:30
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `barbearia`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `agendamentos`
--

CREATE TABLE `agendamentos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `data_agendamento` date NOT NULL,
  `hora_agendamento` time NOT NULL,
  `servico` varchar(255) NOT NULL,
  `tecnico` varchar(255) DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `status` enum('Agendado','Concluído','Cancelado') DEFAULT 'Agendado',
  `observacao` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `agendamentos`
--

INSERT INTO `agendamentos` (`id`, `cliente_id`, `data_agendamento`, `hora_agendamento`, `servico`, `tecnico`, `valor`, `status`, `observacao`) VALUES
(1, 1, '2025-11-27', '15:00:00', 'Corte de Cabelo', 'jackson', 25.00, 'Concluído', ''),
(2, 1, '2025-12-01', '16:00:00', 'Corte de Cabelo', 'jackson', 25.00, 'Concluído', '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `atividades`
--

CREATE TABLE `atividades` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `tipo` varchar(50) NOT NULL,
  `descricao` text NOT NULL,
  `data_atividade` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `atividades`
--

INSERT INTO `atividades` (`id`, `usuario_id`, `tipo`, `descricao`, `data_atividade`) VALUES
(1, 1, 'exclusao_venda', 'Venda excluída: teste para caio amigo', '2025-12-01 15:21:42');

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  `data_cadastro` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`id`, `nome`, `telefone`, `email`, `data_nascimento`, `observacao`, `data_cadastro`) VALUES
(1, 'caio amigo', '81984045412', 't4747226@gmail.com', '1978-04-20', 'vip', '2025-11-27');

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_exclusoes`
--

CREATE TABLE `historico_exclusoes` (
  `id` int(11) NOT NULL,
  `tabela` varchar(50) NOT NULL,
  `registro_id` int(11) NOT NULL,
  `dados` text DEFAULT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `data_exclusao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `historico_exclusoes`
--

INSERT INTO `historico_exclusoes` (`id`, `tabela`, `registro_id`, `dados`, `motivo`, `usuario_id`, `data_exclusao`) VALUES
(1, 'vendas', 4, '{\"id\":4,\"cliente\":\"caio amigo\",\"produto\":\"teste\",\"quantidade\":1,\"valor_unitario\":\"30.00\",\"valor_total\":\"30.00\",\"data_venda\":\"2025-12-01 15:19:24\",\"observacao\":\"\"}', 'Exclusão via painel', 1, '2025-12-01 15:21:42');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `preco_custo` decimal(10,2) DEFAULT 0.00,
  `preco_venda` decimal(10,2) NOT NULL,
  `quantidade_estoque` int(11) DEFAULT 0,
  `quantidade_minima` int(11) DEFAULT 0,
  `codigo_barras` varchar(100) DEFAULT NULL,
  `fornecedor` varchar(255) DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('Ativo','Inativo') DEFAULT 'Ativo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `descricao`, `categoria`, `preco_custo`, `preco_venda`, `quantidade_estoque`, `quantidade_minima`, `codigo_barras`, `fornecedor`, `data_cadastro`, `data_atualizacao`, `status`) VALUES
(6, 'teste', 'mmm', 'barba', 0.00, 30.00, 5, 2, '123456789', 'marcos', '2025-12-01 13:51:58', '2025-12-01 18:21:42', 'Ativo'),
(8, 'creme de barbear', '', 'barba', 15.00, 30.00, 20, 2, '123456', 'marcos', '2025-12-01 17:48:28', '2025-12-01 17:48:28', 'Ativo'),
(9, 'creme alisamento', 'creme', 'cabelo', 20.00, 40.00, 5, 2, '123456789', 'marcos', '2025-12-01 18:26:09', '2025-12-01 18:27:58', 'Ativo');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `cpf` varchar(20) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `senha_crip` varchar(255) NOT NULL,
  `nivel` enum('Administrador','Usuario') NOT NULL,
  `ativo` enum('Sim','Não') DEFAULT 'Sim',
  `data` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `cpf`, `senha`, `senha_crip`, `nivel`, `ativo`, `data`) VALUES
(1, 'Jose Marcos', 'contato@barbershop.com', '000.000.000-00', '123', '202cb962ac59075b964b07152d234b70', 'Administrador', 'Sim', '2025-11-27'),
(3, 'teste1', 'marsilva319@gmail.com', NULL, '1234', '81dc9bdb52d04dc20036dbd8313ed055', 'Usuario', 'Sim', '2025-11-27');

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas`
--

CREATE TABLE `vendas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `valor_unitario` decimal(10,2) NOT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `data_venda` timestamp NOT NULL DEFAULT current_timestamp(),
  `observacao` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `vendas`
--

INSERT INTO `vendas` (`id`, `cliente_id`, `produto_id`, `quantidade`, `valor_unitario`, `valor_total`, `data_venda`, `observacao`) VALUES
(2, 1, 6, 1, 30.00, 30.00, '2025-12-01 13:55:39', ''),
(3, 1, 6, 16, 30.00, 480.00, '2025-12-01 17:40:50', ''),
(5, 1, 9, 8, 40.00, 320.00, '2025-12-01 18:27:08', '');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Índices de tabela `atividades`
--
ALTER TABLE `atividades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `historico_exclusoes`
--
ALTER TABLE `historico_exclusoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `vendas`
--
ALTER TABLE `vendas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `atividades`
--
ALTER TABLE `atividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `historico_exclusoes`
--
ALTER TABLE `historico_exclusoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `vendas`
--
ALTER TABLE `vendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD CONSTRAINT `agendamentos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);

--
-- Restrições para tabelas `atividades`
--
ALTER TABLE `atividades`
  ADD CONSTRAINT `atividades_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `historico_exclusoes`
--
ALTER TABLE `historico_exclusoes`
  ADD CONSTRAINT `historico_exclusoes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `vendas`
--
ALTER TABLE `vendas`
  ADD CONSTRAINT `vendas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `vendas_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
