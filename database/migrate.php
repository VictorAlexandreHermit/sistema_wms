<?php
/**
 * WMS Agiliza - Executor de Migrations de Banco de Dados
 * 
 * Pode ser executado via CLI (`php database/migrate.php`) ou via Front Controller.
 */

define('WMS_EXEC', true);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers/LogHelper.php';

echo "=== WMS Agiliza - Migrations Runner ===\n";

try {
    $pdo = Database::getConnection();

    // 1. Garante que a tabela schema_migrations exista
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS schema_migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 2. Busca migrations já executadas
    $stmt = $pdo->query("SELECT migration FROM schema_migrations");
    $executed = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 3. Lê todos os arquivos SQL na pasta database/migrations/
    $migrationsDir = __DIR__ . '/migrations';
    $files = glob($migrationsDir . '/*.sql');
    sort($files);

    $count = 0;
    foreach ($files as $file) {
        $filename = basename($file);

        if (in_array($filename, $executed)) {
            continue;
        }

        echo "Executando migration: {$filename}... ";

        $sql = file_get_contents($file);

        $pdo->beginTransaction();
        try {
            $pdo->exec($sql);
            
            $stmtInsert = $pdo->prepare("INSERT INTO schema_migrations (migration) VALUES (:migration)");
            $stmtInsert->execute([':migration' => $filename]);

            $pdo->commit();
            echo "[ OK ]\n";
            $count++;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            echo "[ FALHA ]\n";
            echo "Erro: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    echo "Concluído. Total de {$count} migration(s) executada(s).\n";

} catch (\Throwable $e) {
    echo "Erro fatal de banco de dados: " . $e->getMessage() . "\n";
    exit(1);
}
