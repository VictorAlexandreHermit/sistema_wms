<?php
if (!defined('WMS_EXEC')) {
    http_response_code(403);
    die('Acesso direto não permitido.');
}

require_once __DIR__ . '/../models/EnderecoModel.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../helpers/CsrfHelper.php';
require_once __DIR__ . '/../helpers/SanitizeHelper.php';

class EnderecoController {
    /**
     * Exibe a listagem de endereços cadastrados
     */
    public static function index(): void {
        AuthHelper::requireLogin();

        $search = $_GET['q'] ?? null;
        $enderecos = EnderecoModel::all($search);

        $config = require __DIR__ . '/../../config/config.php';
        $baseUrl = $config['app']['base_url'];

        $title = "Cadastro de Endereços - WMS Agiliza";
        $currentRoute = "/enderecos";

        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/templates/sidebar.php';
        require __DIR__ . '/../views/enderecos/index.php';
        require __DIR__ . '/../views/templates/footer.php';
    }

    /**
     * Exibe formulário e processa a criação de um endereço
     */
    public static function create(): void {
        AuthHelper::requireLogin();

        $erro = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CsrfHelper::validate($_POST['csrf_token'] ?? null)) {
                $erro = "Token CSRF inválido ou expirado. Tente novamente.";
            } else {
                $rua = trim($_POST['rua'] ?? '');
                $predio = trim($_POST['predio'] ?? '');
                $nivel = trim($_POST['nivel'] ?? '');
                $capacidadeMaxima = (int)($_POST['capacidade_maxima'] ?? 1000);

                if (empty($rua) || empty($predio) || empty($nivel)) {
                    $erro = "Por favor, preencha todos os componentes do endereço (Rua, Prédio e Nível).";
                } elseif (EnderecoModel::findByFormat($rua, $predio, $nivel) !== null) {
                    $erro = "Já existe um endereço ativo cadastrado nesta posição ('Rua {$rua} - Prédio {$predio} - Nível {$nivel}').";
                } else {
                    EnderecoModel::create([
                        'rua' => $rua,
                        'predio' => $predio,
                        'nivel' => $nivel,
                        'capacidade_maxima' => $capacidadeMaxima
                    ]);

                    $format = sprintf("R%s-P%s-N%s", str_pad($rua, 2, '0', STR_PAD_LEFT), str_pad($predio, 2, '0', STR_PAD_LEFT), str_pad($nivel, 2, '0', STR_PAD_LEFT));
                    $_SESSION['flash_sucesso'] = "Endereço '{$format}' cadastrado com sucesso!";
                    $config = require __DIR__ . '/../../config/config.php';
                    header('Location: ' . $config['app']['base_url'] . '/enderecos');
                    exit;
                }
            }
        }

        $config = require __DIR__ . '/../../config/config.php';
        $baseUrl = $config['app']['base_url'];

        $title = "Novo Endereço - WMS Agiliza";
        $currentRoute = "/enderecos";
        $endereco = null; // Modo criação

        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/templates/sidebar.php';
        require __DIR__ . '/../views/enderecos/form.php';
        require __DIR__ . '/../views/templates/footer.php';
    }

    /**
     * Exibe formulário e processa a edição de um endereço
     */
    public static function edit(): void {
        AuthHelper::requireLogin();

        $id = (int)($_GET['id'] ?? 0);
        $endereco = EnderecoModel::findById($id);

        if (!$endereco) {
            http_response_code(404);
            echo "<h1>404 - Endereço Não Encontrado</h1>";
            exit;
        }

        $erro = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CsrfHelper::validate($_POST['csrf_token'] ?? null)) {
                $erro = "Token CSRF inválido ou expirado. Tente novamente.";
            } else {
                $rua = trim($_POST['rua'] ?? '');
                $predio = trim($_POST['predio'] ?? '');
                $nivel = trim($_POST['nivel'] ?? '');
                $capacidadeMaxima = (int)($_POST['capacidade_maxima'] ?? 1000);

                if (empty($rua) || empty($predio) || empty($nivel)) {
                    $erro = "Por favor, preencha todos os campos do endereço.";
                } elseif (EnderecoModel::findByFormat($rua, $predio, $nivel, $id) !== null) {
                    $erro = "A posição 'Rua {$rua} - Prédio {$predio} - Nível {$nivel}' já pertence a outro endereço.";
                } else {
                    EnderecoModel::update($id, [
                        'rua' => $rua,
                        'predio' => $predio,
                        'nivel' => $nivel,
                        'capacidade_maxima' => $capacidadeMaxima
                    ]);

                    $format = sprintf("R%s-P%s-N%s", str_pad($rua, 2, '0', STR_PAD_LEFT), str_pad($predio, 2, '0', STR_PAD_LEFT), str_pad($nivel, 2, '0', STR_PAD_LEFT));
                    $_SESSION['flash_sucesso'] = "Endereço '{$format}' atualizado com sucesso!";
                    $config = require __DIR__ . '/../../config/config.php';
                    header('Location: ' . $config['app']['base_url'] . '/enderecos');
                    exit;
                }
            }
        }

        $config = require __DIR__ . '/../../config/config.php';
        $baseUrl = $config['app']['base_url'];

        $title = "Editar Endereço - WMS Agiliza";
        $currentRoute = "/enderecos";

        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/templates/sidebar.php';
        require __DIR__ . '/../views/enderecos/form.php';
        require __DIR__ . '/../views/templates/footer.php';
    }

    /**
     * Processa a exclusão lógica (Soft Delete) de um endereço
     */
    public static function delete(): void {
        AuthHelper::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (CsrfHelper::validate($_POST['csrf_token'] ?? null)) {
                $id = (int)($_POST['id'] ?? 0);
                $endereco = EnderecoModel::findById($id);
                if ($endereco) {
                    // Não permite apagar o endereço especial de Quarentena
                    if ($endereco['rua'] === 'QUA' && $endereco['predio'] === '00' && $endereco['nivel'] === '00') {
                        $_SESSION['flash_erro'] = "O endereço virtual especial de Quarentena não pode ser excluído.";
                    } else {
                        EnderecoModel::softDelete($id);
                        $format = sprintf("R%s-P%s-N%s", str_pad($endereco['rua'], 2, '0', STR_PAD_LEFT), str_pad($endereco['predio'], 2, '0', STR_PAD_LEFT), str_pad($endereco['nivel'], 2, '0', STR_PAD_LEFT));
                        $_SESSION['flash_sucesso'] = "Endereço '{$format}' removido com sucesso!";
                    }
                }
            } else {
                $_SESSION['flash_erro'] = "Token CSRF inválido ao tentar excluir o endereço.";
            }
        }

        $config = require __DIR__ . '/../../config/config.php';
        header('Location: ' . $config['app']['base_url'] . '/enderecos');
        exit;
    }
}
