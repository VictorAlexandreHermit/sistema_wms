<?php
if (!defined('WMS_EXEC')) {
    http_response_code(403);
    die('Acesso direto não permitido.');
}

$isEdit = !empty($usuario);
$actionUrl = $isEdit ? $baseUrl . '/usuarios/editar?id=' . $usuario['id'] : $baseUrl . '/usuarios/criar';
?>
<main class="wms-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold"><?= $isEdit ? 'Editar Usuário' : 'Novo Usuário' ?></h1>
            <p class="text-muted small mb-0"><?= $isEdit ? 'Altere as credenciais ou perfil de acesso do colaborador' : 'Cadastre uma nova conta de operador ou gestor' ?></p>
        </div>
        <a href="<?= SanitizeHelper::escape($baseUrl) ?>/usuarios" class="btn btn-wms-secondary">
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
                    <label for="matricula" class="form-label fw-semibold">Matrícula (Login) <span class="text-danger">*</span></label>
                    <input type="text" id="matricula" name="matricula" class="form-control tnum" placeholder="Ex: OPER01 ou GESTOR01" required value="<?= SanitizeHelper::escape($_POST['matricula'] ?? ($usuario['matricula'] ?? '')) ?>">
                    <div class="form-text">Matrícula única utilizada para autenticação no sistema.</div>
                </div>

                <div class="col-md-6">
                    <label for="nome_completo" class="form-label fw-semibold">Nome Completo <span class="text-danger">*</span></label>
                    <input type="text" id="nome_completo" name="nome_completo" class="form-control" placeholder="Ex: Carlos Alberto da Silva" required value="<?= SanitizeHelper::escape($_POST['nome_completo'] ?? ($usuario['nome_completo'] ?? '')) ?>">
                </div>

                <div class="col-md-6">
                    <label for="perfil" class="form-label fw-semibold">Perfil de Acesso (RBAC) <span class="text-danger">*</span></label>
                    <select id="perfil" name="perfil" class="form-select" required>
                        <?php $perfilAtual = $_POST['perfil'] ?? ($usuario['perfil'] ?? 'OPERADOR'); ?>
                        <option value="OPERADOR" <?= $perfilAtual === 'OPERADOR' ? 'selected' : '' ?>>OPERADOR - Acesso operacional a recebimento, guarda, picking e avarias</option>
                        <option value="GESTOR" <?= $perfilAtual === 'GESTOR' ? 'selected' : '' ?>>GESTOR - Acesso total (aprovação de exceções, auditoria, usuários e dashboard)</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="senha" class="form-label fw-semibold">
                        <?= $isEdit ? 'Nova Senha (opcional)' : 'Senha de Acesso *' ?>
                    </label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="<?= $isEdit ? 'Deixe em branco para manter a senha atual' : 'Mínimo de 6 caracteres' ?>" <?= $isEdit ? '' : 'required' ?>>
                    <?php if ($isEdit): ?>
                        <div class="form-text">Preencha somente se desejar alterar a senha do usuário.</div>
                    <?php else: ?>
                        <div class="form-text">A senha será criptografada com algoritmo seguro (BCrypt).</div>
                    <?php endif; ?>
                </div>

                <div class="col-12 text-end mt-4">
                    <a href="<?= SanitizeHelper::escape($baseUrl) ?>/usuarios" class="btn btn-wms-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-wms-primary">
                        <?= $isEdit ? 'Salvar Alterações' : 'Cadastrar Usuário' ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</main>
