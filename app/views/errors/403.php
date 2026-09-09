<?php if (!defined('WMS_EXEC')) { http_response_code(403); die('Acesso direto não permitido.'); } ?>
<?php 
$config = require __DIR__ . '/../../../config/config.php';
$baseUrl = $config['app']['base_url'];
$title = "403 - Acesso Negado";
require_once __DIR__ . '/../templates/header.php';
?>
<div class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow-sm border-danger text-center p-5" style="max-width: 450px; border-radius: 4px;">
        <h1 class="text-danger fw-bold display-4 mb-3">403</h1>
        <h4 class="mb-4">Acesso Negado</h4>
        <p class="text-muted mb-4">Você não tem permissão para acessar esta funcionalidade com o seu perfil atual.</p>
        <a href="<?= SanitizeHelper::escape($baseUrl) ?>/" class="btn w-100 text-white fw-bold" style="background-color: #0F172A; border-color: #0F172A;">Voltar ao Início</a>
    </div>
</div>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
