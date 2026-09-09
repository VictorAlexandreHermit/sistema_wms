<?php
/**
 * WMS Agiliza - Executor de Migrations de Banco de Dados
 */

if (!defined('WMS_EXEC')) {
    define('WMS_EXEC', true);
}

ob_implicit_flush(true);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers/LogHelper.php';

function outputLine(string $msg): void {
    echo $msg . "\n";
    flush();
}

outputLine("=== WMS Agiliza - Migrations Runner ===");

try {
    $pdo = Database::getConnection();

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS schema_migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $stmt = $pdo->query("SELECT migration FROM schema_migrations");
    $executed = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $migrationsDir = __DIR__ . '/migrations';
    $files = glob($migrationsDir . '/*.sql');
    sort($files);

    $count = 0;
    foreach ($files as $file) {
        $filename = basename($file);

        if (in_array($filename, $executed)) {
            outputLine("[ SKIP ] Migration {$filename} já foi executada.");
            continue;
        }

        outputLine("Executando migration: {$filename}...");

        $sqlRaw = file_get_contents($file);

        // Remove comentários SQL do tipo '-- ...' de cada linha
        $lines = explode("\n", $sqlRaw);
        $cleanLines = [];
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            if (str_starts_with($trimmedLine, '--')) {
                continue;
            }
            $cleanLines[] = $line;
        }
        $sqlClean = implode("\n", $cleanLines);

        // Divide pelas instruções SQL terminadas em ';'
        $statements = array_filter(
            array_map('trim', explode(';', $sqlClean)),
            fn($stmt) => !empty($stmt)
        );

        try {
            foreach ($statements as $statement) {
                $pdo->exec($statement);
            }
            
            $stmtInsert = $pdo->prepare("INSERT INTO schema_migrations (migration) VALUES (:migration)");
            $stmtInsert->execute([':migration' => $filename]);

            outputLine("[ OK ] Migration {$filename} executada com sucesso.");
            $count++;
        } catch (\Throwable $e) {
            outputLine("[ FALHA ] Erro em {$filename}: " . $e->getMessage());
            exit(1);
        }
    }

    outputLine("Concluído. Total de {$count} migration(s) executada(s).");

} catch (\Throwable $e) {
    outputLine("Erro fatal: " . $e->getMessage());
    exit(1);
}
