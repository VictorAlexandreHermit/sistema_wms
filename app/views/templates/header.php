<?php
defined('WMS_EXEC') or die('Acesso direto proibido.');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SanitizeHelper::escape($title ?? 'WMS Agiliza') ?></title>

    <!-- Bootstrap 5 Local -->
    <link rel="stylesheet" href="<?= SanitizeHelper::escape($baseUrl ?? '/sistema_wms') ?>/assets/css/bootstrap.min.css">
    
    <!-- Precision Logistics Custom Styles -->
    <link rel="stylesheet" href="<?= SanitizeHelper::escape($baseUrl ?? '/sistema_wms') ?>/assets/css/style.css">
</head>
<body>
<div class="wms-wrapper">
