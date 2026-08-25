<?php
defined('WMS_EXEC') or die('Acesso direto proibido.');

class AuthHelper {
    /**
     * Retorna se existe um usuário logado na sessão atual
     */
    public static function isLoggedIn(): bool {
        return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
    }

    /**
     * Exige que o usuário esteja autenticado, redirecionando para /login se não estiver
     */
    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            header('Location: ' . self::getBaseUrl() . '/login');
            exit;
        }
    }

    /**
     * Exige que o usuário tenha um perfil específico (ex: 'GESTOR')
     */
    public static function requirePerfil(string $perfilRequerido): void {
        self::requireLogin();

        $perfilAtual = $_SESSION['usuario_perfil'] ?? '';
        if ($perfilAtual !== $perfilRequerido) {
            LogHelper::registrarSeguranca(
                'ACESSO_NEGADO_PERFIL',
                sprintf("Perfil '%s' tentou acessar recurso restrito a '%s'", $perfilAtual, $perfilRequerido)
            );

            http_response_code(403);
            if (file_exists(__DIR__ . '/../views/errors/403.php')) {
                require __DIR__ . '/../views/errors/403.php';
            } else {
                echo "<h1>403 - Acesso Negado</h1>";
                echo "<p>Você não possui permissão para acessar esta funcionalidade.</p>";
            }
            exit;
        }
    }

    /**
     * Retorna a URL base configurada
     */
    private static function getBaseUrl(): string {
        $config = require __DIR__ . '/../../config/config.php';
        return $config['app']['base_url'] ?? '';
    }
}
