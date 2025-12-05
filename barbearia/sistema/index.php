<?php
require_once("conexao.php");

//inserir um usuário padrão se não existir nenhum no banco
$senha = '123';
$senha_crip = md5($senha);

$query = $pdo->query("SELECT * FROM usuarios where nivel = 'Administrador' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg == 0) {
    // Corrigido: usar $email_sistema em vez de $nome_sistema no email
    $pdo->query("INSERT INTO usuarios SET nome = 'Jose Marcos', email = '$email_sistema',
        cpf = '000.000.000-00', senha = '$senha', senha_crip = '$senha_crip', 
        nivel = 'Administrador', data = curDate() ");
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title><?php echo $nome_sistema ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/style-login.css">
    <link rel="icon" type="image/png" href="img/favicon.ico">

</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <!-- Logo adicionada aqui -->
            <div class="logo-container text-center mb-4">
                <img src="img/logo.png" alt="Logo <?php echo $nome_sistema ?>" class="login-logo">
                <h1 class="system-name mt-2"><?php echo $nome_sistema ?></h1>
            </div>
            
            <h2 class="text-center mb-4">Acesso ao Sistema</h2>
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
            <?php endif; ?>
            <form action="autenticar.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Usuário</label>
                    <input type="text" class="form-control" placeholder="E-mail ou CPF" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" class="form-control" placeholder="Senha" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-dark w-100">Entrar</button>

                <p class="mt-3 text-center">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#modalEsqueceuSenha">Esqueceu sua senha?</a>
                </p>
            </form>
        </div>
    </div>

    <!-- Modal Esqueceu Senha -->
    <div class="modal fade" id="modalEsqueceuSenha" tabindex="-1" aria-labelledby="modalEsqueceuSenhaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEsqueceuSenhaLabel">Recuperar Senha</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="recuperar_senha.php" method="POST">
                    <div class="modal-body">
                        <p>Digite seu e-mail cadastrado para recuperar a senha:</p>
                        <div class="mb-3">
                            <label for="email_recuperacao" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email_recuperacao" name="email" placeholder="seu@email.com" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Enviar Link de Recuperação</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts do Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>