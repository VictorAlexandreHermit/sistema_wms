<?php if (!defined('WMS_EXEC')) { http_response_code(403); die('Acesso direto não permitido.'); } ?>
<div class="d-flex min-vh-100">
    <!-- Sidebar -->
    <aside class="wms-sidebar text-white p-3 d-flex flex-column" style="background-color: #0F172A; width: 250px;">
        <div class="mb-4 text-center">
            <h5 class="fw-bold mb-0">WMS Agiliza</h5>
            <small class="text-muted">Gestão de Armazém</small>
        </div>
        
        <nav class="nav flex-column gap-2 mb-auto">
            <a href="<?= SanitizeHelper::escape($baseUrl) ?>/produtos" class="nav-link text-white <?= ($currentRoute ?? '') === '/produtos' ? 'bg-secondary rounded' : '' ?>">Produtos</a>
            <a href="<?= SanitizeHelper::escape($baseUrl) ?>/enderecos" class="nav-link text-white <?= ($currentRoute ?? '') === '/enderecos' ? 'bg-secondary rounded' : '' ?>">Endereços</a>
            
            <?php if (($_SESSION['usuario_perfil'] ?? '') === 'GESTOR'): ?>
            <a href="<?= SanitizeHelper::escape($baseUrl) ?>/usuarios" class="nav-link text-white <?= ($currentRoute ?? '') === '/usuarios' ? 'bg-secondary rounded' : '' ?>">Usuários</a>
            <?php endif; ?>
        </nav>

        <div class="mt-auto border-top border-secondary pt-3">
            <div class="small mb-3 text-truncate">
                Logado como:<br>
                <strong><?= SanitizeHelper::escape($_SESSION['usuario_nome'] ?? 'Usuário') ?></strong>
                <br>(<?= SanitizeHelper::escape($_SESSION['usuario_perfil'] ?? '') ?>)
            </div>
            <form method="POST" action="<?= SanitizeHelper::escape($baseUrl) ?>/logout">
                <?= CsrfHelper::input() ?>
                <button type="submit" class="btn btn-sm btn-outline-light w-100">Sair</button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-grow-1 p-4 overflow-auto">
