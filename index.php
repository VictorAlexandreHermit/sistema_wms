<?php
/**
 * WMS Agiliza - Front Controller Único
 * 
 * Todas as requisições web são canalizadas por este arquivo.
 */

// Define constante global de controle de segurança de execução
define('WMS_EXEC', true);

// Carrega a configuração do sistema
$config = require_once __DIR__ . '/config/config.php';

// Carrega os helpers essenciais de infraestrutura
require_once __DIR__ . '/app/helpers/SessionHelper.php';
require_once __DIR__ . '/app/helpers/SanitizeHelper.php';
require_once __DIR__ . '/app/helpers/CsrfHelper.php';
require_once __DIR__ . '/app/helpers/LogHelper.php';
require_once __DIR__ . '/app/helpers/AuthHelper.php';

// Inicializa a sessão segura
SessionHelper::init();

// Captura a URI da requisição para roteamento
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$route = '/' . trim(str_replace($scriptName, '', $requestUri), '/');

// Roteamento temporário de infraestrutura (Fase 1)
switch ($route) {
    case '/':
    case '/login':
        // Rota inicial / Login
        if (file_exists(__DIR__ . '/app/views/auth/login.php')) {
            require __DIR__ . '/app/views/auth/login.php';
        } else {
            echo "<h1>WMS Agiliza - Sistema de Gerenciamento de Armazém</h1>";
            echo "<p>Infraestrutura inicial da Fase 1 pronta com sucesso.</p>";
        }
        break;
    default:
        http_response_code(404);
        echo "<h1>404 - Página não encontrada</h1>";
        break;
}
