<?php
defined('WMS_EXEC') or die('Acesso direto proibido.');

class SessionHelper {
    private const SESSION_TIMEOUT = 28800; // 8 horas em segundos (8 * 3600)

    /**
     * Inicializa a sessão com parâmetros seguros
     */
    public static function init(): void {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            
            session_start();
        }

        // Verifica inatividade e expira a sessão após 8 horas
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > self::SESSION_TIMEOUT)) {
            self::destroy();
            return;
        }

        $_SESSION['LAST_ACTIVITY'] = time();
    }

    /**
     * Regenera o ID de sessão após autenticação
     */
    public static function regenerate(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    /**
     * Encerra e destrói a sessão atual
     */
    public static function destroy(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }
            session_destroy();
        }
    }
}
