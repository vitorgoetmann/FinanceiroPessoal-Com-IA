<?php
$appRoot = dirname(dirname(__FILE__));

// Carregar .env (se existir)
$envPath = $appRoot . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }
        $key = trim($parts[0]);
        $value = trim($parts[1]);
        $value = trim($value, "\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Configurações do banco de dados - XAMPP LOCALHOST (com suporte a Railway)
define('DB_HOST', getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'financeiro_vg');
define('DB_USER', getenv('DB_USER') ?: getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

// Configurações da aplicação
define('APP_NAME', 'Controle Financeiro VG');
define('BASE_URL', 'http://localhost/controlefinanceiro/');
define('APP_ROOT', $appRoot);

// Configurações de IA (API externa)
define('AI_PROVIDER', getenv('AI_PROVIDER') ?: 'groq');
define('AI_API_URL', getenv('AI_API_URL') ?: 'https://api.groq.com/openai/v1/chat/completions');
define('AI_MODEL', getenv('AI_MODEL') ?: 'llama-3.1-8b-instant');
define('AI_API_KEY', getenv('AI_API_KEY') ?: '');

// Paleta de cores para uso no sistema
define('PALETTE_COLORS', [
    '#006064', '#00897B', '#26A69A', '#4DB6AC', '#80CBC4', '#B2DFDB', '#E0F2F1',
    '#00838F', '#00ACC1', '#26C6DA', '#4DD0E1', '#80DEEA', '#B2EBF2',
    '#1B5E20', '#2E7D32', '#388E3C', '#43A047', '#4CAF50', '#66BB6A', '#81C784',
    '#A5D6A7', '#C8E6C9', '#558B2F', '#689F38', '#7CB342', '#8BC34A', '#9CCC65',
    '#00695C', '#00796B', '#009688', '#26A69A', '#4DB6AC', '#80CBC4',
    '#01579B', '#0277BD', '#0288D1', '#039BE5', '#03A9F4', '#29B6F6'
]);

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Autoload de classes
spl_autoload_register(function($className) {
    $paths = [
        APP_ROOT . '/app/controllers/' . $className . '.php',
        APP_ROOT . '/app/models/' . $className . '.php',
        APP_ROOT . '/app/core/' . $className . '.php',
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});
