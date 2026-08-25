<?php
defined('WMS_EXEC') or die('Acesso direto proibido.');
$currentRoute = $currentRoute ?? '';
?>
<aside class="wms-sidebar">
    <div class="brand">
        WMS AGILIZA
    </div>
    <nav class="nav flex-column mt-3">
        <a class="nav-link <?= $currentRoute === '/kanban' ? 'active' : '' ?>" href="/kanban">
            <span>Quadro Kanban</span>
        </a>
        <a class="nav-link <?= $currentRoute === '/recebimento' ? 'active' : '' ?>" href="/recebimento">
            <span>Recebimento</span>
        </a>
        <a class="nav-link <?= $currentRoute === '/guarda' ? 'active' : '' ?>" href="/guarda">
            <span>Instruções de Guarda</span>
        </a>
        <a class="nav-link <?= $currentRoute === '/separacao' ? 'active' : '' ?>" href="/separacao/conferir">
            <span>Picking & Packing</span>
        </a>
        <a class="nav-link <?= $currentRoute === '/avarias' ? 'active' : '' ?>" href="/avarias">
            <span>Quarentena / Avarias</span>
        </a>
        <a class="nav-link <?= $currentRoute === '/auditoria' ? 'active' : '' ?>" href="/auditoria">
            <span>Auditoria de Estoque</span>
        </a>
        <?php if (($_SESSION['usuario_perfil'] ?? '') === 'GESTOR'): ?>
            <a class="nav-link <?= $currentRoute === '/dashboard' ? 'active' : '' ?>" href="/dashboard">
                <span>Dashboard Executivo</span>
            </a>
        <?php endif; ?>
        <a class="nav-link mt-5 text-danger" href="/logout">
            <span>Sair do Sistema</span>
        </a>
    </nav>
</aside>
