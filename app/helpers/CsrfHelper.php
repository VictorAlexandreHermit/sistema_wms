<?php
defined('WMS_EXEC') or die('Acesso direto proibido.');

class CsrfHelper {
    /**
     * Gera e retorna o token CSRF atual da sessão
     */
    public static function getToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Retorna o campo de input HTML oculto com o token CSRF
     */
    public static function input(): string {
        $token = self::getToken();
        return sprintf('<input type="hidden" name="csrf_token" value="%s">', SanitizeHelper::escape($token));
    }

    /**
     * Valida se o token enviado via POST corresponde ao token da sessão
     */
    public static function validate(?string $token): bool {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
