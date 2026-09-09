<?php
if (!defined('WMS_EXEC')) {
    http_response_code(403);
    die('Acesso direto não permitido.');
}
?>
<main class="wms-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">Gestão de Usuários</h1>
            <p class="text-muted small mb-0">Administração de operadores e gestores do WMS Agiliza</p>
        </div>
        <a href="<?= SanitizeHelper::escape($baseUrl) ?>/usuarios/criar" class="btn btn-wms-primary">
            + Novo Usuário
        </a>
    </div>

    <?php if (!empty($_SESSION['flash_sucesso'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= SanitizeHelper::escape($_SESSION['flash_sucesso']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash_sucesso']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_erro'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= SanitizeHelper::escape($_SESSION['flash_erro']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash_erro']); ?>
    <?php endif; ?>

    <!-- Card com Filtro de Pesquisa -->
    <div class="card-wms mb-4">
        <form method="GET" action="<?= SanitizeHelper::escape($baseUrl) ?>/usuarios" class="row g-2 align-items-center">
            <div class="col-md-9">
                <input type="text" name="q" class="form-control" placeholder="Pesquisar por Matrícula ou Nome Completo..." value="<?= SanitizeHelper::escape($_GET['q'] ?? '') ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-wms-secondary w-100">Filtrar</button>
                <?php if (!empty($_GET['q'])): ?>
                    <a href="<?= SanitizeHelper::escape($baseUrl) ?>/usuarios" class="btn btn-outline-secondary">Limpar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabela de Usuários -->
    <div class="card-wms p-0 overflow-hidden">
        <table class="table-wms mb-0">
            <thead>
                <tr>
                    <th>Matrícula</th>
                    <th>Nome Completo</th>
                    <th class="text-center">Perfil de Acesso</th>
                    <th>Data de Cadastro</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            Nenhum usuário cadastrado ou encontrado.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $u): ?>
                        <?php $isSelf = ((int)$u['id'] === (int)($_SESSION['usuario_id'] ?? 0)); ?>
                        <tr>
                            <td class="fw-bold tnum"><?= SanitizeHelper::escape($u['matricula']) ?></td>
                            <td>
                                <?= SanitizeHelper::escape($u['nome_completo']) ?>
                                <?php if ($isSelf): ?>
                                    <span class="badge bg-primary ms-1">Você</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($u['perfil'] === 'GESTOR'): ?>
                                    <span class="badge-status badge-warning">GESTOR</span>
                                <?php else: ?>
                                    <span class="badge-status badge-info">OPERADOR</span>
                                <?php endif; ?>
                            </td>
                            <td class="tnum small text-muted"><?= date('d/m/Y H:i', strtotime($u['created_at'])) ?></td>
                            <td class="text-end">
                                <a href="<?= SanitizeHelper::escape($baseUrl) ?>/usuarios/editar?id=<?= $u['id'] ?>" class="btn btn-sm btn-wms-secondary me-1">
                                    Editar
                                </a>
                                <?php if (!$isSelf): ?>
                                    <form method="POST" action="<?= SanitizeHelper::escape($baseUrl) ?>/usuarios/excluir" class="d-inline" onsubmit="return confirm('Deseja realmente desativar o usuário <?= SanitizeHelper::escape($u['matricula']) ?>?');">
                                        <?= CsrfHelper::input() ?>
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Desativar
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary" disabled>Ativo</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
