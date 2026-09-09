<?php
if (!defined('WMS_EXEC')) {
    http_response_code(403);
    die('Acesso direto não permitido.');
}

require_once __DIR__ . '/../../config/database.php';

class EnderecoModel {
    /**
     * Retorna todos os endereços ativos (deleted_at IS NULL)
     */
    public static function all(?string $search = null): array {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM enderecos WHERE deleted_at IS NULL";
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $sql .= " AND (rua LIKE :search OR predio LIKE :search OR nivel LIKE :search)";
            $params[':search'] = '%' . trim($search) . '%';
        }

        $sql .= " ORDER BY rua ASC, predio ASC, nivel ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca um endereço pelo ID
     */
    public static function findById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM enderecos WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Busca endereço por Rua, Prédio e Nível (para validação de unicidade)
     */
    public static function findByFormat(string $rua, string $predio, string $nivel, ?int $ignoreId = null): ?array {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM enderecos WHERE rua = :rua AND predio = :predio AND nivel = :nivel AND deleted_at IS NULL";
        $params = [
            ':rua' => strtoupper(trim($rua)),
            ':predio' => strtoupper(trim($predio)),
            ':nivel' => strtoupper(trim($nivel))
        ];

        if ($ignoreId !== null) {
            $sql .= " AND id != :ignore_id";
            $params[':ignore_id'] = $ignoreId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Cadastra um novo endereço físico
     */
    public static function create(array $data): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO enderecos (rua, predio, nivel, capacidade_maxima)
            VALUES (:rua, :predio, :nivel, :capacidade_maxima)
        ");
        $stmt->execute([
            ':rua' => strtoupper(trim($data['rua'])),
            ':predio' => strtoupper(trim($data['predio'])),
            ':nivel' => strtoupper(trim($data['nivel'])),
            ':capacidade_maxima' => (int)($data['capacidade_maxima'] ?? 1000)
        ]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Atualiza os dados de um endereço
     */
    public static function update(int $id, array $data): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE enderecos 
            SET rua = :rua,
                predio = :predio,
                nivel = :nivel,
                capacidade_maxima = :capacidade_maxima
            WHERE id = :id AND deleted_at IS NULL
        ");
        return $stmt->execute([
            ':id' => $id,
            ':rua' => strtoupper(trim($data['rua'])),
            ':predio' => strtoupper(trim($data['predio'])),
            ':nivel' => strtoupper(trim($data['nivel'])),
            ':capacidade_maxima' => (int)($data['capacidade_maxima'] ?? 1000)
        ]);
    }

    /**
     * Realiza a exclusão lógica (Soft Delete)
     */
    public static function softDelete(int $id): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE enderecos 
            SET deleted_at = CURRENT_TIMESTAMP 
            WHERE id = :id AND deleted_at IS NULL
        ");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Auxiliar para formatar o código completo do endereço (Ex: R01-P02-N03)
     */
    public static function formatCodigo(array $endereco): string {
        return sprintf(
            "%s-%s-%s",
            str_pad($endereco['rua'], 2, '0', STR_PAD_LEFT),
            str_pad($endereco['predio'], 2, '0', STR_PAD_LEFT),
            str_pad($endereco['nivel'], 2, '0', STR_PAD_LEFT)
        );
    }
}
