<?php
defined('WMS_EXEC') or die('Acesso direto proibido.');

class LogHelper {
    /**
     * Registra erros e exceções técnicas com contingência em arquivo
     */
    public static function registrarErro(\Throwable $e): void {
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        
        try {
            // Tenta gravar no banco de dados MySQL na tabela logs_erro
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO logs_erro (mensagem, arquivo, linha, trace, usuario_id)
                VALUES (:mensagem, :arquivo, :linha, :trace, :usuario_id)
            ");
            $stmt->execute([
                ':mensagem'   => $e->getMessage(),
                ':arquivo'    => $e->getFile(),
                ':linha'      => $e->getLine(),
                ':trace'      => $e->getTraceAsString(),
                ':usuario_id' => $usuarioId
            ]);
        } catch (\Throwable $dbException) {
            // Contingência em arquivo físico caso o MySQL esteja indisponível
            $logPath = __DIR__ . '/../../logs/error.log';
            $entry = sprintf(
                "[%s] ERRO BANCO UNREACHABLE: %s em %s:%d (Original Error: %s)\n",
                date('Y-m-d H:i:s'),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $dbException->getMessage()
            );
            @file_put_contents($logPath, $entry, FILE_APPEND | LOCK_EX);
        }
    }

    /**
     * Registra eventos de segurança (login inválido, acesso negado, etc.)
     */
    public static function registrarSeguranca(string $evento, ?string $detalhes = null): void {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $usuarioId = $_SESSION['usuario_id'] ?? null;

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO logs_seguranca (evento, ip_origem, usuario_id, detalhes)
                VALUES (:evento, :ip_origem, :usuario_id, :detalhes)
            ");
            $stmt->execute([
                ':evento'     => $evento,
                ':ip_origem'  => $ip,
                ':usuario_id' => $usuarioId,
                ':detalhes'   => $detalhes
            ]);
        } catch (\Throwable $e) {
            // Log de contingência em arquivo para eventos de segurança
            $logPath = __DIR__ . '/../../logs/security.log';
            $entry = sprintf(
                "[%s] SEGURANCA [%s] IP: %s | User: %s | Details: %s\n",
                date('Y-m-d H:i:s'),
                $evento,
                $ip,
                $usuarioId ?? 'ANON',
                $detalhes ?? ''
            );
            @file_put_contents($logPath, $entry, FILE_APPEND | LOCK_EX);
        }
    }
}
