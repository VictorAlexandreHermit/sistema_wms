<?php if (!defined('WMS_EXEC')) { http_response_code(403); die('Acesso direto não permitido.'); } ?>
    <?php if (isset($currentRoute)): ?>
        </div> <!-- Fecha flex-grow-1 -->
    </div> <!-- Fecha d-flex min-vh-100 -->
    <?php endif; ?>
    
    <script src="<?= SanitizeHelper::escape($baseUrl) ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
