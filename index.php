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

// Carrega os Controllers dos Cadastros Base (Fase 2)
require_once __DIR__ . '/app/controllers/ProdutoController.php';
require_once __DIR__ . '/app/controllers/EnderecoController.php';
require_once __DIR__ . '/app/controllers/UsuarioController.php';
require_once __DIR__ . '/app/controllers/AuthController.php';

// Inicializa a sessão segura
SessionHelper::init();

// Captura a URI da requisição para roteamento
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($scriptName === '/') {
    $scriptName = '';
}
$route = '/' . trim(str_replace($scriptName, '', $requestUri), '/');

// Dispatcher de Rotas da Aplicação
switch ($route) {
    case '/':
    case '/login':
        AuthController::login();
        break;
    
    case '/logout':
        AuthController::logout();
        break;

    // Rota Utilitária de Seeder de Dados
    case '/seed':
        require __DIR__ . '/database/seed.php';
        break;

    // Rotas do Módulo de Produtos
    case '/produtos':
        ProdutoController::index();
        break;
    case '/produtos/criar':
        ProdutoController::create();
        break;
    case '/produtos/editar':
        ProdutoController::edit();
        break;
    case '/produtos/excluir':
        ProdutoController::delete();
        break;

    // Rotas do Módulo de Endereços
    case '/enderecos':
        EnderecoController::index();
        break;
    case '/enderecos/criar':
        EnderecoController::create();
        break;
    case '/enderecos/editar':
        EnderecoController::edit();
        break;
    case '/enderecos/excluir':
        EnderecoController::delete();
        break;

    // Rotas do Módulo de Usuários
    case '/usuarios':
        UsuarioController::index();
        break;
    case '/usuarios/criar':
        UsuarioController::create();
        break;
    case '/usuarios/editar':
        UsuarioController::edit();
        break;
    case '/usuarios/excluir':
        UsuarioController::delete();
        break;

    default:
        http_response_code(404);
        echo "<h1>404 - Página não encontrada</h1>";
        break;
}
