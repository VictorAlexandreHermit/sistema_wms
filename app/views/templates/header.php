<?php if (!defined('WMS_EXEC')) { http_response_code(403); die('Acesso direto não permitido.'); } ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SanitizeHelper::escape($title ?? 'WMS Agiliza') ?></title>
    <link rel="stylesheet" href="<?= SanitizeHelper::escape($baseUrl) ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= SanitizeHelper::escape($baseUrl) ?>/assets/css/style.css">
</head>
<body style="background-color: #F8FAFC; font-family: 'Inter', sans-serif;">
