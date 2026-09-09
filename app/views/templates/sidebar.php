<?php
if (!defined('WMS_EXEC')) {
    http_response_code(403);
    die('Acesso direto não permitido.');
}
$currentRoute = $currentRoute ?? '';
$baseUrl = $baseUrl ?? '/sistema_wms';
?>
<aside class="wms-sidebar">
    <div class="brand">
        WMS AGILIZA
    </div>
    <nav class="nav flex-column mt-3">
        <a class="nav-link <?= str_starts_with($currentRoute, '/produtos') ? 'active' : '' ?>" href="<?= SanitizeHelper::escape($baseUrl) ?>/produtos">
            <span>Produtos</span>
        </a>
        <a class="nav-link <?= str_starts_with($currentRoute, '/enderecos') ? 'active' : '' ?>" href="<?= SanitizeHelper::escape($baseUrl) ?>/enderecos">
            <span>Endereços Físicos</span>
        </a>
        <?php if (($_SESSION['usuario_perfil'] ?? '') === 'GESTOR'): ?>
            <a class="nav-link <?= str_starts_with($currentRoute, '/usuarios') ? 'active' : '' ?>" href="<?= SanitizeHelper::escape($baseUrl) ?>/usuarios">
                <span>Gestão de Usuários</span>
            </a>
        <?php endif; ?>

        <div class="px-3 my-2 text-uppercase text-muted small fw-bold" style="font-size: 0.65rem; letter-spacing: 0.05em;">Operações</div>

        <a class="nav-link <?= $currentRoute === '/kanban' ? 'active' : '' ?>" href="<?= SanitizeHelper::escape($baseUrl) ?>/kanban">
            <span>Quadro Kanban</span>
        </a>
        <a class="nav-link <?= $currentRoute === '/recebimento' ? 'active' : '' ?>" href="<?= SanitizeHelper::escape($baseUrl) ?>/recebimento">
            <span>Recebimento</span>
        </a>
        <a class="nav-link <?= $currentRoute === '/guarda' ? 'active' : '' ?>" href="<?= SanitizeHelper::escape($baseUrl) ?>/guarda">
            <span>Instruções de Guarda</span>
        </a>
        <a class="nav-link <?= $currentRoute === '/separacao' ? 'active' : '' ?>" href="<?= SanitizeHelper::escape($baseUrl) ?>/separacao/conferir">
            <span>Picking & Packing</span>
        </a>
        <a class="nav-link <?= $currentRoute === '/avarias' ? 'active' : '' ?>" href="<?= SanitizeHelper::escape($baseUrl) ?>/avarias">
            <span>Quarentena / Avarias</span>
        </a>
        <a class="nav-link <?= $currentRoute === '/auditoria' ? 'active' : '' ?>" href="<?= SanitizeHelper::escape($baseUrl) ?>/auditoria">
            <span>Auditoria de Estoque</span>
        </a>
        <?php if (($_SESSION['usuario_perfil'] ?? '') === 'GESTOR'): ?>
            <a class="nav-link <?= $currentRoute === '/dashboard' ? 'active' : '' ?>" href="<?= SanitizeHelper::escape($baseUrl) ?>/dashboard">
                <span>Dashboard Executivo</span>
            </a>
        <?php endif; ?>
        <a class="nav-link mt-4 text-danger" href="<?= SanitizeHelper::escape($baseUrl) ?>/logout">
            <span>Sair do Sistema</span>
        </a>
    </nav>
</aside>
