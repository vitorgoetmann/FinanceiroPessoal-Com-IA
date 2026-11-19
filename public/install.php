<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - Controle Financeiro</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #006064 0%, #00897B 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
        
        h1 {
            color: #00897B;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #757575;
            margin-bottom: 30px;
        }
        
        .step {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #00897B;
        }
        
        .step h3 {
            color: #00897B;
            margin-bottom: 10px;
        }
        
        .step code {
            background: #212121;
            color: #4CAF50;
            padding: 10px 15px;
            display: block;
            border-radius: 6px;
            margin: 10px 0;
            font-size: 13px;
            overflow-x: auto;
        }
        
        .status {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .status.success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #4CAF50;
        }
        
        .status.error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #f44336;
        }
        
        .status.warning {
            background: #fff3e0;
            color: #e65100;
            border-left: 4px solid #ff9800;
        }
        
        .btn {
            display: inline-block;
            background: #00897B;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            margin-top: 20px;
        }
        
        .btn:hover {
            background: #006064;
        }
        
        ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        li {
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Instalação do Sistema</h1>
        <p class="subtitle">Controle Financeiro Pessoal</p>

        <?php
        // Verificar requisitos
        $phpVersion = version_compare(PHP_VERSION, '8.0.0', '>=');
        $pdoInstalled = extension_loaded('pdo') && extension_loaded('pdo_mysql');
        $htaccessExists = file_exists('.htaccess');
        
        if ($phpVersion && $pdoInstalled):
        ?>
            <div class="status success">
                <strong>✓ Requisitos atendidos!</strong><br>
                PHP: <?= PHP_VERSION ?><br>
                PDO MySQL: Instalado
            </div>
        <?php else: ?>
            <div class="status error">
                <strong>✗ Requisitos não atendidos</strong><br>
                <?php if (!$phpVersion): ?>
                    PHP: <?= PHP_VERSION ?> (Necessário: 8.0+)<br>
                <?php endif; ?>
                <?php if (!$pdoInstalled): ?>
                    PDO MySQL: Não instalado<br>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!$htaccessExists): ?>
            <div class="status warning">
                <strong>⚠ Arquivo .htaccess não encontrado</strong><br>
                Certifique-se de que o arquivo existe na raiz do projeto.
            </div>
        <?php endif; ?>

        <div class="step">
            <h3>📋 Passo 1: Criar o Banco de Dados</h3>
            <p>Execute os comandos SQL abaixo ou use o phpMyAdmin:</p>
            <code>
                CREATE DATABASE financeiro_db CHARACTER SET utf8mb4;<br>
                USE financeiro_db;
            </code>
            <p>Depois, importe o arquivo <strong>database.sql</strong></p>
        </div>

        <div class="step">
            <h3>⚙️ Passo 2: Configurar Credenciais</h3>
            <p>Edite o arquivo: <strong>config/config.php</strong></p>
            <p>Ajuste as credenciais do banco de dados se necessário:</p>
            <code>
                DB_HOST = 'localhost'<br>
                DB_NAME = 'financeiro_db'<br>
                DB_USER = 'root'<br>
                DB_PASS = ''
            </code>
        </div>

        <div class="step">
            <h3>🔧 Passo 3: Ativar mod_rewrite</h3>
            <p>No Apache (XAMPP/WAMP), certifique-se de que o mod_rewrite está ativo:</p>
            <ul>
                <li>Abra httpd.conf</li>
                <li>Descomente: LoadModule rewrite_module</li>
                <li>Reinicie o Apache</li>
            </ul>
        </div>

        <div class="step">
            <h3>✅ Passo 4: Acessar o Sistema</h3>
            <p>Após configurar, acesse:</p>
            <code>http://localhost/controlefinanceiro/</code>
        </div>

        <a href="auth/login" class="btn">
            Ir para o Sistema →
        </a>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; color: #757575; font-size: 13px;">
            <strong>Dica:</strong> Após a instalação, você pode excluir este arquivo (install.php) por segurança.
        </div>
    </div>
</body>
</html>
