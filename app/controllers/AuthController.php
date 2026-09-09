<?php
if (!defined('WMS_EXEC')) {
    http_response_code(403);
    die('Acesso direto não permitido.');
}

require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../helpers/CsrfHelper.php';
require_once __DIR__ . '/../helpers/SanitizeHelper.php';
require_once __DIR__ . '/../helpers/SessionHelper.php';
require_once __DIR__ . '/../helpers/LogHelper.php';

class AuthController {
    /**
     * Exibe formulário e processa o login
     */
    public static function login(): void {
        if (AuthHelper::isLoggedIn()) {
            $config = require __DIR__ . '/../../config/config.php';
            header('Location: ' . $config['app']['base_url'] . '/produtos');
            exit;
        }

        $erro = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CsrfHelper::validate($_POST['csrf_token'] ?? null)) {
                $erro = "Token inválido ou expirado. Tente novamente.";
            } else {
                $matricula = trim($_POST['matricula'] ?? '');
                $senha = $_POST['senha'] ?? '';

                $usuario = UsuarioModel::findByMatricula($matricula);
                if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
                    // Autenticado com sucesso
                    SessionHelper::regenerate();
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nome'] = $usuario['nome_completo'];
                    $_SESSION['usuario_perfil'] = $usuario['perfil'];

                    LogHelper::registrarSeguranca('LOGIN_SUCESSO', "Matrícula: {$matricula}");

                    $config = require __DIR__ . '/../../config/config.php';
                    header('Location: ' . $config['app']['base_url'] . '/produtos');
                    exit;
                } else {
                    $erro = "Matrícula ou senha incorretos.";
                    LogHelper::registrarSeguranca('LOGIN_FALHA', "Matrícula tentada: {$matricula}");
                }
            }
        }

        $config = require __DIR__ . '/../../config/config.php';
        $baseUrl = $config['app']['base_url'];
        $title = "Login - WMS Agiliza";
        // currentRoute omitido para não renderizar o flex container extra do layout logado

        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/auth/login.php';
        require __DIR__ . '/../views/templates/footer.php';
    }

    /**
     * Encerra a sessão do usuário
     */
    public static function logout(): void {
        $usuarioId = $_SESSION['usuario_id'] ?? 'desconhecido';
        if ($usuarioId !== 'desconhecido') {
            LogHelper::registrarSeguranca('LOGOUT', "Usuário ID: {$usuarioId}");
        }
        SessionHelper::destroy();

        $config = require __DIR__ . '/../../config/config.php';
        header('Location: ' . $config['app']['base_url'] . '/login');
        exit;
    }
}
