<?php if (!defined('WMS_EXEC')) { http_response_code(403); die('Acesso direto não permitido.'); } ?>
<div class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow-sm" style="width: 100%; max-width: 400px; border-radius: 4px; border: 1px solid #E2E8F0;">
        <div class="card-body p-4 bg-white">
            <h2 class="text-center fw-bold mb-4" style="color: #0F172A;">WMS Agiliza</h2>
            <h5 class="text-center text-muted mb-4">Login de Acesso</h5>
            
            <?php if (!empty($erro)): ?>
                <div class="alert alert-danger p-2 text-center small"><?= SanitizeHelper::escape($erro) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="<?= SanitizeHelper::escape($baseUrl) ?>/login">
                <?= CsrfHelper::input() ?>
                <div class="mb-3">
                    <label for="matricula" class="form-label fw-bold">Matrícula</label>
                    <input type="text" id="matricula" name="matricula" class="form-control" required autofocus>
                </div>
                <div class="mb-4">
                    <label for="senha" class="form-label fw-bold">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control" required>
                </div>
                <button type="submit" class="btn text-white w-100 fw-bold" style="background-color: #0F172A; border-color: #0F172A;">Entrar</button>
            </form>
        </div>
    </div>
</div>
