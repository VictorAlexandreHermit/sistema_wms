<?php
/**
 * WMS Agiliza - Executor de Seeder de Dados Iniciais
 * 
 * Insere registros obrigatórios para funcionamento inicial da aplicação.
 * Pode ser executado via CLI (`php database/seed.php`) ou via Front Controller.
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

outputLine("=== WMS Agiliza - Seeder Runner ===");

try {
    $pdo = Database::getConnection();

    // 1. Inserção do Usuário Gestor padrão (ADMIN01)
    $stmtUser = $pdo->prepare("SELECT id FROM usuarios WHERE matricula = :matricula");
    $stmtUser->execute([':matricula' => 'ADMIN01']);
    if (!$stmtUser->fetch()) {
        $stmtInsertUser = $pdo->prepare("
            INSERT INTO usuarios (matricula, senha_hash, nome_completo, perfil)
            VALUES (:matricula, :senha_hash, :nome_completo, :perfil)
        ");
        $stmtInsertUser->execute([
            ':matricula' => 'ADMIN01',
            ':senha_hash' => password_hash('Gestor@123', PASSWORD_BCRYPT),
            ':nome_completo' => 'Gestor Padrão do Sistema',
            ':perfil' => 'GESTOR'
        ]);
        outputLine("[ OK ] Usuário Gestor (ADMIN01) inserido com sucesso.");
    } else {
        outputLine("[ INFO ] Usuário Gestor (ADMIN01) já cadastrado.");
    }

    // 2. Inserção do Usuário Operador padrão (OPER01)
    $stmtOper = $pdo->prepare("SELECT id FROM usuarios WHERE matricula = :matricula");
    $stmtOper->execute([':matricula' => 'OPER01']);
    if (!$stmtOper->fetch()) {
        $stmtInsertOper = $pdo->prepare("
            INSERT INTO usuarios (matricula, senha_hash, nome_completo, perfil)
            VALUES (:matricula, :senha_hash, :nome_completo, :perfil)
        ");
        $stmtInsertOper->execute([
            ':matricula' => 'OPER01',
            ':senha_hash' => password_hash('Operador@123', PASSWORD_BCRYPT),
            ':nome_completo' => 'Operador Galpão 01',
            ':perfil' => 'OPERADOR'
        ]);
        outputLine("[ OK ] Usuário Operador (OPER01) inserido com sucesso.");
    } else {
        outputLine("[ INFO ] Usuário Operador (OPER01) já cadastrado.");
    }

    // 3. Inserção dos SLAs padrão de Kanban (120 minutos)
    $etapas = ['RECEBIDO', 'A_ARMAZENAR', 'A_SEPARAR', 'A_EXPEDIR'];
    $stmtSla = $pdo->prepare("SELECT id FROM configuracoes_sla WHERE etapa_kanban = :etapa");
    $stmtInsertSla = $pdo->prepare("
        INSERT INTO configuracoes_sla (etapa_kanban, tempo_limite_minutos)
        VALUES (:etapa, :tempo)
    ");

    foreach ($etapas as $etapa) {
        $stmtSla->execute([':etapa' => $etapa]);
        if (!$stmtSla->fetch()) {
            $stmtInsertSla->execute([
                ':etapa' => $etapa,
                ':tempo' => 120
            ]);
            outputLine("[ OK ] SLA para etapa '{$etapa}' configurado (120 min).");
        } else {
            outputLine("[ INFO ] SLA para etapa '{$etapa}' já configurado.");
        }
    }

    // 4. Inserção do Endereço Virtual especial "Quarentena" (QUA-00-00)
    $stmtQuarentena = $pdo->prepare("SELECT id FROM enderecos WHERE rua = 'QUA' AND predio = '00' AND nivel = '00'");
    $stmtQuarentena->execute();
    if (!$stmtQuarentena->fetch()) {
        $stmtInsertQuarentena = $pdo->prepare("
            INSERT INTO enderecos (rua, predio, nivel, capacidade_maxima)
            VALUES ('QUA', '00', '00', 999999)
        ");
        $stmtInsertQuarentena->execute();
        outputLine("[ OK ] Endereço virtual de Quarentena (QUA-00-00) inserido com sucesso.");
    } else {
        outputLine("[ INFO ] Endereço virtual de Quarentena (QUA-00-00) já cadastrado.");
    }

    outputLine("Carga inicial de dados finalizada com sucesso!");

} catch (\Throwable $e) {
    outputLine("[ ERRO ] Falha ao executar o seeder: " . $e->getMessage());
    if (class_exists('LogHelper')) {
        LogHelper::registrarErro($e);
    }
    exit(1);
}
