<?php
if (!defined('WMS_EXEC')) {
    http_response_code(403);
    die('Acesso direto não permitido.');
}

require_once __DIR__ . '/../../config/database.php';

class ConfiguracaoSlaModel {
    /**
     * Retorna todas as configurações de SLA por etapa
     */
    public static function all(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM configuracoes_sla ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna a configuração de SLA para uma etapa específica do Kanban
     */
    public static function getByEtapa(string $etapa): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM configuracoes_sla WHERE etapa_kanban = :etapa");
        $stmt->execute([':etapa' => strtoupper(trim($etapa))]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Atualiza o tempo limite em minutos de uma etapa do Kanban
     */
    public static function update(string $etapa, int $tempoLimiteMinutos, ?int $updatedBy = null): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE configuracoes_sla 
            SET tempo_limite_minutos = :tempo,
                updated_by = :updated_by,
                updated_at = CURRENT_TIMESTAMP
            WHERE etapa_kanban = :etapa
        ");
        return $stmt->execute([
            ':etapa' => strtoupper(trim($etapa)),
            ':tempo' => $tempoLimiteMinutos,
            ':updated_by' => $updatedBy
        ]);
    }
}
