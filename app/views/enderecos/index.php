<?php
if (!defined('WMS_EXEC')) {
    http_response_code(403);
    die('Acesso direto não permitido.');
}
?>
<main class="wms-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">Cadastro de Endereços Físicos</h1>
            <p class="text-muted small mb-0">Mapeamento da estrutura de galpão (Rua - Prédio - Nível)</p>
        </div>
        <a href="<?= SanitizeHelper::escape($baseUrl) ?>/enderecos/criar" class="btn btn-wms-primary">
            + Novo Endereço
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
        <form method="GET" action="<?= SanitizeHelper::escape($baseUrl) ?>/enderecos" class="row g-2 align-items-center">
            <div class="col-md-9">
                <input type="text" name="q" class="form-control" placeholder="Pesquisar por Rua, Prédio ou Nível..." value="<?= SanitizeHelper::escape($_GET['q'] ?? '') ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-wms-secondary w-100">Filtrar</button>
                <?php if (!empty($_GET['q'])): ?>
                    <a href="<?= SanitizeHelper::escape($baseUrl) ?>/enderecos" class="btn btn-outline-secondary">Limpar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabela de Endereços -->
    <div class="card-wms p-0 overflow-hidden">
        <table class="table-wms mb-0">
            <thead>
                <tr>
                    <th>Código Formatado</th>
                    <th class="text-center">Rua</th>
                    <th class="text-center">Prédio</th>
                    <th class="text-center">Nível</th>
                    <th class="text-end">Capacidade Máx.</th>
                    <th class="text-center">Tipo</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($enderecos)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            Nenhum endereço físico cadastrado ou encontrado.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($enderecos as $e): ?>
                        <?php
                            $isQuarentena = ($e['rua'] === 'QUA' && $e['predio'] === '00' && $e['nivel'] === '00');
                            $format = sprintf("R%s-P%s-N%s", str_pad($e['rua'], 2, '0', STR_PAD_LEFT), str_pad($e['predio'], 2, '0', STR_PAD_LEFT), str_pad($e['nivel'], 2, '0', STR_PAD_LEFT));
                        ?>
                        <tr>
                            <td class="fw-bold tnum">
                                <span class="badge bg-dark text-white px-2 py-1"><?= SanitizeHelper::escape($format) ?></span>
                            </td>
                            <td class="text-center tnum"><?= SanitizeHelper::escape($e['rua']) ?></td>
                            <td class="text-center tnum"><?= SanitizeHelper::escape($e['predio']) ?></td>
                            <td class="text-center tnum"><?= SanitizeHelper::escape($e['nivel']) ?></td>
                            <td class="text-end tnum"><?= number_format($e['capacidade_maxima'], 0, ',', '.') ?> vol</td>
                            <td class="text-center">
                                <?php if ($isQuarentena): ?>
                                    <span class="badge-status badge-danger">Virtual / Quarentena</span>
                                <?php else: ?>
                                    <span class="badge-status badge-success">Físico / Galpão</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if (!$isQuarentena): ?>
                                    <a href="<?= SanitizeHelper::escape($baseUrl) ?>/enderecos/editar?id=<?= $e['id'] ?>" class="btn btn-sm btn-wms-secondary me-1">
                                        Editar
                                    </a>
                                    <form method="POST" action="<?= SanitizeHelper::escape($baseUrl) ?>/enderecos/excluir" class="d-inline" onsubmit="return confirm('Deseja realmente remover o endereço <?= SanitizeHelper::escape($format) ?>?');">
                                        <?= CsrfHelper::input() ?>
                                        <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Excluir
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted small">Protegido</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
