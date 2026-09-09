<?php
if (!defined('WMS_EXEC')) {
    http_response_code(403);
    die('Acesso direto não permitido.');
}

$isEdit = !empty($endereco);
$actionUrl = $isEdit ? $baseUrl . '/enderecos/editar?id=' . $endereco['id'] : $baseUrl . '/enderecos/criar';
?>
<main class="wms-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold"><?= $isEdit ? 'Editar Endereço Físico' : 'Novo Endereço Físico' ?></h1>
            <p class="text-muted small mb-0"><?= $isEdit ? 'Altere a posição ou capacidade do endereço' : 'Cadastre uma nova posição de estocagem no galpão' ?></p>
        </div>
        <a href="<?= SanitizeHelper::escape($baseUrl) ?>/enderecos" class="btn btn-wms-secondary">
            &larr; Voltar
        </a>
    </div>

    <?php if (!empty($erro)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= SanitizeHelper::escape($erro) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card-wms">
        <form method="POST" action="<?= SanitizeHelper::escape($actionUrl) ?>">
            <?= CsrfHelper::input() ?>

            <div class="row g-3">
                <div class="col-md-4">
                    <label for="rua" class="form-label fw-semibold">Rua (Corredor) <span class="text-danger">*</span></label>
                    <input type="text" id="rua" name="rua" class="form-control tnum" placeholder="Ex: 01 ou R01" required value="<?= SanitizeHelper::escape($_POST['rua'] ?? ($endereco['rua'] ?? '')) ?>">
                    <div class="form-text">Identificador do corredor de armazenamento.</div>
                </div>

                <div class="col-md-4">
                    <label for="predio" class="form-label fw-semibold">Prédio (Estante) <span class="text-danger">*</span></label>
                    <input type="text" id="predio" name="predio" class="form-control tnum" placeholder="Ex: 02 ou P02" required value="<?= SanitizeHelper::escape($_POST['predio'] ?? ($endereco['predio'] ?? '')) ?>">
                    <div class="form-text">Identificador do prédio/módulo na rua.</div>
                </div>

                <div class="col-md-4">
                    <label for="nivel" class="form-label fw-semibold">Nível (Prateleira) <span class="text-danger">*</span></label>
                    <input type="text" id="nivel" name="nivel" class="form-control tnum" placeholder="Ex: 03 ou N03" required value="<?= SanitizeHelper::escape($_POST['nivel'] ?? ($endereco['nivel'] ?? '')) ?>">
                    <div class="form-text">Identificador da prateleira/altura.</div>
                </div>

                <div class="col-md-6">
                    <label for="capacidade_maxima" class="form-label fw-semibold">Capacidade Máxima (Volumes)</label>
                    <input type="number" id="capacidade_maxima" name="capacidade_maxima" class="form-control tnum" min="1" max="999999" value="<?= SanitizeHelper::escape($_POST['capacidade_maxima'] ?? ($endereco['capacidade_maxima'] ?? 1000)) ?>">
                    <div class="form-text">Quantidade máxima de volumes suportada nesta posição física.</div>
                </div>

                <div class="col-12 text-end mt-4">
                    <a href="<?= SanitizeHelper::escape($baseUrl) ?>/enderecos" class="btn btn-wms-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-wms-primary">
                        <?= $isEdit ? 'Salvar Alterações' : 'Cadastrar Endereço' ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</main>
