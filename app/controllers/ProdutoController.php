<?php
if (!defined('WMS_EXEC')) {
    http_response_code(403);
    die('Acesso direto não permitido.');
}

require_once __DIR__ . '/../models/ProdutoModel.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../helpers/CsrfHelper.php';
require_once __DIR__ . '/../helpers/SanitizeHelper.php';

class ProdutoController {
    /**
     * Exibe a listagem de produtos cadastrados
     */
    public static function index(): void {
        AuthHelper::requireLogin();

        $search = $_GET['q'] ?? null;
        $produtos = ProdutoModel::all($search);

        $config = require __DIR__ . '/../../config/config.php';
        $baseUrl = $config['app']['base_url'];

        $title = "Cadastro de Produtos - WMS Agiliza";
        $currentRoute = "/produtos";

        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/templates/sidebar.php';
        require __DIR__ . '/../views/produtos/index.php';
        require __DIR__ . '/../views/templates/footer.php';
    }

    /**
     * Exibe formulário e processa a criação de um produto
     */
    public static function create(): void {
        AuthHelper::requireLogin();

        $erro = null;
        $sucesso = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CsrfHelper::validate($_POST['csrf_token'] ?? null)) {
                $erro = "Token CSRF inválido ou expirado. Tente novamente.";
            } else {
                $sku = trim($_POST['sku'] ?? '');
                $codigoBarras = trim($_POST['codigo_barras'] ?? '');
                $descricao = trim($_POST['descricao'] ?? '');
                $unidadeMedida = trim($_POST['unidade_medida'] ?? 'UN');
                $curvaAbc = trim($_POST['curva_abc'] ?? 'C');

                if (empty($sku) || empty($codigoBarras) || empty($descricao)) {
                    $erro = "Por favor, preencha todos os campos obrigatórios (SKU, Código de Barras e Descrição).";
                } elseif (ProdutoModel::findBySku($sku) !== null) {
                    $erro = "Já existe um produto ativo cadastrado com o SKU informado ('{$sku}').";
                } elseif (ProdutoModel::findByCodigoBarras($codigoBarras) !== null) {
                    $erro = "Já existe um produto ativo cadastrado com o Código de Barras informado ('{$codigoBarras}').";
                } else {
                    $id = ProdutoModel::create([
                        'sku' => $sku,
                        'codigo_barras' => $codigoBarras,
                        'descricao' => $descricao,
                        'unidade_medida' => $unidadeMedida,
                        'curva_abc' => $curvaAbc,
                        'created_by' => $_SESSION['usuario_id'] ?? null
                    ]);

                    $_SESSION['flash_sucesso'] = "Produto SKU '{$sku}' cadastrado com sucesso!";
                    $config = require __DIR__ . '/../../config/config.php';
                    header('Location: ' . $config['app']['base_url'] . '/produtos');
                    exit;
                }
            }
        }

        $config = require __DIR__ . '/../../config/config.php';
        $baseUrl = $config['app']['base_url'];

        $title = "Novo Produto - WMS Agiliza";
        $currentRoute = "/produtos";
        $produto = null; // Modo criação

        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/templates/sidebar.php';
        require __DIR__ . '/../views/produtos/form.php';
        require __DIR__ . '/../views/templates/footer.php';
    }

    /**
     * Exibe formulário e processa a edição de um produto existente
     */
    public static function edit(): void {
        AuthHelper::requireLogin();

        $id = (int)($_GET['id'] ?? 0);
        $produto = ProdutoModel::findById($id);

        if (!$produto) {
            http_response_code(404);
            echo "<h1>404 - Produto Não Encontrado</h1>";
            exit;
        }

        $erro = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CsrfHelper::validate($_POST['csrf_token'] ?? null)) {
                $erro = "Token CSRF inválido ou expirado. Tente novamente.";
            } else {
                $sku = trim($_POST['sku'] ?? '');
                $codigoBarras = trim($_POST['codigo_barras'] ?? '');
                $descricao = trim($_POST['descricao'] ?? '');
                $unidadeMedida = trim($_POST['unidade_medida'] ?? 'UN');
                $curvaAbc = trim($_POST['curva_abc'] ?? 'C');

                if (empty($sku) || empty($codigoBarras) || empty($descricao)) {
                    $erro = "Por favor, preencha todos os campos obrigatórios.";
                } elseif (ProdutoModel::findBySku($sku, $id) !== null) {
                    $erro = "O SKU '{$sku}' já está sendo utilizado por outro produto.";
                } elseif (ProdutoModel::findByCodigoBarras($codigoBarras, $id) !== null) {
                    $erro = "O Código de Barras '{$codigoBarras}' já está sendo utilizado por outro produto.";
                } else {
                    ProdutoModel::update($id, [
                        'sku' => $sku,
                        'codigo_barras' => $codigoBarras,
                        'descricao' => $descricao,
                        'unidade_medida' => $unidadeMedida,
                        'curva_abc' => $curvaAbc,
                        'updated_by' => $_SESSION['usuario_id'] ?? null
                    ]);

                    $_SESSION['flash_sucesso'] = "Produto SKU '{$sku}' atualizado com sucesso!";
                    $config = require __DIR__ . '/../../config/config.php';
                    header('Location: ' . $config['app']['base_url'] . '/produtos');
                    exit;
                }
            }
        }

        $config = require __DIR__ . '/../../config/config.php';
        $baseUrl = $config['app']['base_url'];

        $title = "Editar Produto - WMS Agiliza";
        $currentRoute = "/produtos";

        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/templates/sidebar.php';
        require __DIR__ . '/../views/produtos/form.php';
        require __DIR__ . '/../views/templates/footer.php';
    }

    /**
     * Processa a exclusão lógica (Soft Delete) de um produto
     */
    public static function delete(): void {
        AuthHelper::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (CsrfHelper::validate($_POST['csrf_token'] ?? null)) {
                $id = (int)($_POST['id'] ?? 0);
                $produto = ProdutoModel::findById($id);
                if ($produto) {
                    ProdutoModel::softDelete($id);
                    $_SESSION['flash_sucesso'] = "Produto SKU '{$produto['sku']}' removido com sucesso!";
                }
            } else {
                $_SESSION['flash_erro'] = "Token CSRF inválido ao tentar excluir o produto.";
            }
        }

        $config = require __DIR__ . '/../../config/config.php';
        header('Location: ' . $config['app']['base_url'] . '/produtos');
        exit;
    }
}
