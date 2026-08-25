<?php
defined('WMS_EXEC') or die('Acesso direto proibido.');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WMS Agiliza</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 bg-light">
    <div class="card card-wms p-4 shadow-sm" style="width: 100%; max-width: 400px;">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-dark mb-1">WMS AGILIZA</h3>
            <p class="text-muted small">Acesso ao Sistema de Armazém</p>
        </div>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger p-2 small" role="alert">
                <?= SanitizeHelper::escape($erro) ?>
            </div>
        <?php endif; ?>

        <form action="login" method="POST">
            <?= CsrfHelper::input() ?>
            
            <div class="mb-3">
                <label for="matricula" class="form-label fw-semibold small text-secondary">Matrícula</label>
                <input type="text" class="form-control" id="matricula" name="matricula" required placeholder="Digite sua matrícula">
            </div>

            <div class="mb-4">
                <label for="senha" class="form-label fw-semibold small text-secondary">Senha</label>
                <input type="password" class="form-control" id="senha" name="senha" required placeholder="Digite sua senha">
            </div>

            <button type="submit" class="btn btn-wms-primary w-100">Entrar na Operação</button>
        </form>
    </div>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
