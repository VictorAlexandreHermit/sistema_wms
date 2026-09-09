<?php
if (!defined('WMS_EXEC')) {
    http_response_code(403);
    die('Acesso direto não permitido.');
}

require_once __DIR__ . '/../../config/database.php';

class ProdutoModel {
    /**
     * Retorna todos os produtos ativos (deleted_at IS NULL) com filtro opcional
     */
    public static function all(?string $search = null): array {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM produtos WHERE deleted_at IS NULL";
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $sql .= " AND (sku LIKE :search OR codigo_barras LIKE :search OR descricao LIKE :search)";
            $params[':search'] = '%' . trim($search) . '%';
        }

        $sql .= " ORDER BY sku ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca um produto pelo ID (somente ativo)
     */
    public static function findById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Busca um produto pelo SKU (inclui deletados se idExcluir informada para validação de unicidade)
     */
    public static function findBySku(string $sku, ?int $ignoreId = null): ?array {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM produtos WHERE sku = :sku AND deleted_at IS NULL";
        $params = [':sku' => trim($sku)];

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
     * Busca um produto pelo Código de Barras
     */
    public static function findByCodigoBarras(string $codigoBarras, ?int $ignoreId = null): ?array {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM produtos WHERE codigo_barras = :codigo AND deleted_at IS NULL";
        $params = [':codigo' => trim($codigoBarras)];

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
     * Cadastra um novo produto
     */
    public static function create(array $data): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO produtos (sku, codigo_barras, descricao, unidade_medida, curva_abc, created_by)
            VALUES (:sku, :codigo_barras, :descricao, :unidade_medida, :curva_abc, :created_by)
        ");
        $stmt->execute([
            ':sku' => trim($data['sku']),
            ':codigo_barras' => trim($data['codigo_barras']),
            ':descricao' => trim($data['descricao']),
            ':unidade_medida' => strtoupper(trim($data['unidade_medida'] ?? 'UN')),
            ':curva_abc' => strtoupper(trim($data['curva_abc'] ?? 'C')),
            ':created_by' => $data['created_by'] ?? null
        ]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Atualiza os dados de um produto existente
     */
    public static function update(int $id, array $data): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE produtos 
            SET sku = :sku,
                codigo_barras = :codigo_barras,
                descricao = :descricao,
                unidade_medida = :unidade_medida,
                curva_abc = :curva_abc,
                updated_by = :updated_by,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id AND deleted_at IS NULL
        ");
        return $stmt->execute([
            ':id' => $id,
            ':sku' => trim($data['sku']),
            ':codigo_barras' => trim($data['codigo_barras']),
            ':descricao' => trim($data['descricao']),
            ':unidade_medida' => strtoupper(trim($data['unidade_medida'] ?? 'UN')),
            ':curva_abc' => strtoupper(trim($data['curva_abc'] ?? 'C')),
            ':updated_by' => $data['updated_by'] ?? null
        ]);
    }

    /**
     * Realiza a exclusão lógica (Soft Delete) de um produto
     */
    public static function softDelete(int $id): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE produtos 
            SET deleted_at = CURRENT_TIMESTAMP 
            WHERE id = :id AND deleted_at IS NULL
        ");
        return $stmt->execute([':id' => $id]);
    }
}
