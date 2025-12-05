<?php

$banco = 'barbearia';
$usuario = 'root';
$senha = '';    
$servidor = 'localhost';

date_default_timezone_set('America/Sao_Paulo');

try {
    $pdo = new PDO("mysql:host=$servidor;dbname=$banco;charset=utf8", $usuario, $senha);
   
} catch (PDOException $e) {
    echo "Não conectado ao banco de dados! <br><br> " . $e;
    
}

//variaveis do sistema
$nome_sistema = "JK BARBERSHOP";
$email_sistema = "contato@barbershop.com";
?>