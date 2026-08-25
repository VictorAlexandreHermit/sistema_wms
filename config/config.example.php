<?php
if (!defined('WMS_EXEC')) {
    http_response_code(403);
    die('Acesso direto não permitido.');
}

/**
 * Arquivo de Exemplo de Configuração - WMS Agiliza
 * Copie este arquivo para config.php e ajuste as credenciais do seu ambiente.
 */
return [
    'db' => [
        'host' => 'localhost',
        'dbname' => 'wms_agiliza',
        'user' => 'seu_usuario',
        'pass' => 'sua_senha',
        'charset' => 'utf8mb4'
    ],
    'app' => [
        'name' => 'WMS Agiliza',
        'base_url' => 'http://localhost/sistema_wms',
        'timezone' => 'America/Sao_Paulo'
    ],
    'otif' => [
        'expiracao_dias_uteis' => 10,
        'upload_max_bytes' => 5242880 // 5MB (5 * 1024 * 1024)
    ]
];
