<?php
defined('WMS_EXEC') or die('Acesso direto proibido.');

class Database {
    private static ?PDO $instance = null;

    /**
     * Retorna a conexão PDO Singleton com o banco de dados MySQL
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/config.php';
            $dbConfig = $config['db'];

            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                $dbConfig['host'],
                $dbConfig['dbname'],
                $dbConfig['charset']
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], $options);
            } catch (PDOException $e) {
                // Registra o erro via contingência em arquivo físico se a conexão com o banco de dados falhar (evita recursão infinita no LogHelper)
                $logPath = __DIR__ . '/../logs/error.log';
                $entry = sprintf("[%s] ERRO CONEXAO PDO: %s em %s:%d\n", date('Y-m-d H:i:s'), $e->getMessage(), $e->getFile(), $e->getLine());
                @file_put_contents($logPath, $entry, FILE_APPEND | LOCK_EX);
                throw $e;
            }
        }

        return self::$instance;
    }

    /**
     * Construtor privado para impedir instanciação direta
     */
    private function __construct() {}
}
