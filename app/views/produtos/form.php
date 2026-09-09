<?php
if (!defined('WMS_EXEC')) {
    http_response_code(403);
    die('Acesso direto não permitido.');
}

$isEdit = !empty($produto);
$actionUrl = $isEdit ? $baseUrl . '/produtos/editar?id=' . $produto['id'] : $baseUrl . '/produtos/criar';
?>
<main class="wms-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold"><?= $isEdit ? 'Editar Produto' : 'Novo Produto' ?></h1>
            <p class="text-muted small mb-0"><?= $isEdit ? 'Atualize as informações do item selecionado' : 'Cadastre um novo item no catálogo de produtos' ?></p>
        </div>
        <a href="<?= SanitizeHelper::escape($baseUrl) ?>/produtos" class="btn btn-wms-secondary">
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
                <div class="col-md-6">
                    <label for="sku" class="form-label fw-semibold">Código SKU <span class="text-danger">*</span></label>
                    <input type="text" id="sku" name="sku" class="form-control tnum" placeholder="Ex: SKU-1001" required value="<?= SanitizeHelper::escape($_POST['sku'] ?? ($produto['sku'] ?? '')) ?>">
                    <div class="form-text">Identificador único de estoque (SKU).</div>
                </div>

                <div class="col-md-6">
                    <label for="codigo_barras" class="form-label fw-semibold">Código de Barras (EAN/GTIN) <span class="text-danger">*</span></label>
                    <input type="text" id="codigo_barras" name="codigo_barras" class="form-control tnum" placeholder="Ex: 7891234567890" required value="<?= SanitizeHelper::escape($_POST['codigo_barras'] ?? ($produto['codigo_barras'] ?? '')) ?>">
                    <div class="form-text">Utilizado para validação por leitor de código de barras USB.</div>
                </div>

                <div class="col-12">
                    <label for="descricao" class="form-label fw-semibold">Descrição do Produto <span class="text-danger">*</span></label>
                    <input type="text" id="descricao" name="descricao" class="form-control" placeholder="Ex: Parafuso Sextavado ZB 1/4 x 2 polegadas" required value="<?= SanitizeHelper::escape($_POST['descricao'] ?? ($produto['descricao'] ?? '')) ?>">
                </div>

                <div class="col-md-6">
                    <label for="unidade_medida" class="form-label fw-semibold">Unidade de Medida</label>
                    <select id="unidade_medida" name="unidade_medida" class="form-select">
                        <?php
                            $umAtual = $_POST['unidade_medida'] ?? ($produto['unidade_medida'] ?? 'UN');
                            $unidades = ['UN' => 'UN - Unidade', 'CX' => 'CX - Caixa', 'KG' => 'KG - Quilograma', 'PCT' => 'PCT - Pacote', 'PAR' => 'PAR - Par', 'RL' => 'RL - Rolo'];
                            foreach ($unidades as $val => $label):
                        ?>
                            <option value="<?= $val ?>" <?= $umAtual === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="curva_abc" class="form-label fw-semibold">Classificação Curva ABC</label>
                    <select id="curva_abc" name="curva_abc" class="form-select">
                        <?php
                            $curvaAtual = $_POST['curva_abc'] ?? ($produto['curva_abc'] ?? 'C');
                            $curvas = [
                                'A' => 'Curva A - Alto Giro / Prioridade Máxima no Kanban',
                                'B' => 'Curva B - Giro Médio',
                                'C' => 'Curva C - Baixo Giro / Padrão'
                            ];
                            foreach ($curvas as $val => $label):
                        ?>
                            <option value="<?= $val ?>" <?= $curvaAtual === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Produtos de Curva A sobem automaticamente para o topo na fase de picking.</div>
                </div>

                <div class="col-12 text-end mt-4">
                    <a href="<?= SanitizeHelper::escape($baseUrl) ?>/produtos" class="btn btn-wms-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-wms-primary">
                        <?= $isEdit ? 'Salvar Alterações' : 'Cadastrar Produto' ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</main>
