<?php
defined('WMS_EXEC') or die('Acesso direto proibido.');

class SanitizeHelper {
    /**
     * Sanitiza saídas de texto para prevenir XSS no HTML
     */
    public static function escape(?string $data): string {
        if ($data === null) {
            return '';
        }
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitiza strings para uso em nomes de arquivos e uploads
     */
    public static function filename(string $filename): string {
        $filename = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $filename);
        return basename($filename);
    }
}
