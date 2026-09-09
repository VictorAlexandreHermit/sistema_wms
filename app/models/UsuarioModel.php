<?php
if (!defined('WMS_EXEC')) {
    http_response_code(403);
    die('Acesso direto não permitido.');
}

require_once __DIR__ . '/../../config/database.php';

class UsuarioModel {
    /**
     * Retorna todos os usuários ativos (deleted_at IS NULL)
     */
    public static function all(?string $search = null): array {
        $pdo = Database::getConnection();
        $sql = "SELECT id, matricula, nome_completo, perfil, created_at, updated_at FROM usuarios WHERE deleted_at IS NULL";
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $sql .= " AND (matricula LIKE :search OR nome_completo LIKE :search OR perfil LIKE :search)";
            $params[':search'] = '%' . trim($search) . '%';
        }

        $sql .= " ORDER BY nome_completo ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca um usuário pelo ID
     */
    public static function findById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, matricula, nome_completo, perfil, created_at FROM usuarios WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Busca um usuário pela Matrícula (para autenticação ou verificação de unicidade)
     */
    public static function findByMatricula(string $matricula, ?int $ignoreId = null): ?array {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM usuarios WHERE matricula = :matricula AND deleted_at IS NULL";
        $params = [':matricula' => trim($matricula)];

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
     * Cadastra um novo usuário com senha criptografada via BCrypt
     */
    public static function create(array $data): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (matricula, senha_hash, nome_completo, perfil, created_by)
            VALUES (:matricula, :senha_hash, :nome_completo, :perfil, :created_by)
        ");
        $stmt->execute([
            ':matricula' => trim($data['matricula']),
            ':senha_hash' => password_hash($data['senha'], PASSWORD_BCRYPT),
            ':nome_completo' => trim($data['nome_completo']),
            ':perfil' => strtoupper(trim($data['perfil'])),
            ':created_by' => $data['created_by'] ?? null
        ]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Atualiza dados de um usuário
     */
    public static function update(int $id, array $data): bool {
        $pdo = Database::getConnection();
        
        $sql = "
            UPDATE usuarios 
            SET matricula = :matricula,
                nome_completo = :nome_completo,
                perfil = :perfil,
                updated_by = :updated_by,
                updated_at = CURRENT_TIMESTAMP
        ";
        $params = [
            ':id' => $id,
            ':matricula' => trim($data['matricula']),
            ':nome_completo' => trim($data['nome_completo']),
            ':perfil' => strtoupper(trim($data['perfil'])),
            ':updated_by' => $data['updated_by'] ?? null
        ];

        // Se uma nova senha for fornecida, atualiza o hash
        if (!empty($data['senha'])) {
            $sql .= ", senha_hash = :senha_hash";
            $params[':senha_hash'] = password_hash($data['senha'], PASSWORD_BCRYPT);
        }

        $sql .= " WHERE id = :id AND deleted_at IS NULL";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Realiza a exclusão lógica (Soft Delete) de um usuário
     */
    public static function softDelete(int $id): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE usuarios 
            SET deleted_at = CURRENT_TIMESTAMP 
            WHERE id = :id AND deleted_at IS NULL
        ");
        return $stmt->execute([':id' => $id]);
    }
}
