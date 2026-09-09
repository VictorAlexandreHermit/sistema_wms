<?php
if (!defined('WMS_EXEC')) {
    http_response_code(403);
    die('Acesso direto não permitido.');
}
?>
<main class="wms-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">Cadastro de Produtos</h1>
            <p class="text-muted small mb-0">Gerencie os itens do catálogo armazenados no galpão</p>
        </div>
        <a href="<?= SanitizeHelper::escape($baseUrl) ?>/produtos/criar" class="btn btn-wms-primary">
            + Novo Produto
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
        <form method="GET" action="<?= SanitizeHelper::escape($baseUrl) ?>/produtos" class="row g-2 align-items-center">
            <div class="col-md-9">
                <input type="text" name="q" class="form-control" placeholder="Pesquisar por SKU, Código de Barras ou Descrição..." value="<?= SanitizeHelper::escape($_GET['q'] ?? '') ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-wms-secondary w-100">Filtrar</button>
                <?php if (!empty($_GET['q'])): ?>
                    <a href="<?= SanitizeHelper::escape($baseUrl) ?>/produtos" class="btn btn-outline-secondary">Limpar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabela de Produtos -->
    <div class="card-wms p-0 overflow-hidden">
        <table class="table-wms mb-0">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Código de Barras</th>
                    <th>Descrição</th>
                    <th class="text-center">Unidade</th>
                    <th class="text-center">Curva ABC</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($produtos)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            Nenhum produto cadastrado ou encontrado na pesquisa.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($produtos as $p): ?>
                        <tr>
                            <td class="fw-bold sku-code tnum"><?= SanitizeHelper::escape($p['sku']) ?></td>
                            <td class="barcode-code tnum"><?= SanitizeHelper::escape($p['codigo_barras']) ?></td>
                            <td><?= SanitizeHelper::escape($p['descricao']) ?></td>
                            <td class="text-center"><span class="badge bg-light text-dark border"><?= SanitizeHelper::escape($p['unidade_medida']) ?></span></td>
                            <td class="text-center">
                                <?php
                                    $badgeClass = 'badge-info';
                                    if ($p['curva_abc'] === 'A') $badgeClass = 'badge-danger';
                                    elseif ($p['curva_abc'] === 'B') $badgeClass = 'badge-warning';
                                ?>
                                <span class="badge-status <?= $badgeClass ?>">Curva <?= SanitizeHelper::escape($p['curva_abc']) ?></span>
                            </td>
                            <td class="text-end">
                                <a href="<?= SanitizeHelper::escape($baseUrl) ?>/produtos/editar?id=<?= $p['id'] ?>" class="btn btn-sm btn-wms-secondary me-1">
                                    Editar
                                </a>
                                <form method="POST" action="<?= SanitizeHelper::escape($baseUrl) ?>/produtos/excluir" class="d-inline" onsubmit="return confirm('Deseja realmente remover o produto SKU <?= SanitizeHelper::escape($p['sku']) ?>?');">
                                    <?= CsrfHelper::input() ?>
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        Excluir
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
