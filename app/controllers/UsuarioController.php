<?php
if (!defined('WMS_EXEC')) {
    http_response_code(403);
    die('Acesso direto não permitido.');
}

require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../helpers/CsrfHelper.php';
require_once __DIR__ . '/../helpers/SanitizeHelper.php';

class UsuarioController {
    /**
     * Exibe a listagem de usuários do sistema (Restrito ao perfil GESTOR)
     */
    public static function index(): void {
        AuthHelper::requirePerfil('GESTOR');

        $search = $_GET['q'] ?? null;
        $usuarios = UsuarioModel::all($search);

        $config = require __DIR__ . '/../../config/config.php';
        $baseUrl = $config['app']['base_url'];

        $title = "Gestão de Usuários - WMS Agiliza";
        $currentRoute = "/usuarios";

        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/templates/sidebar.php';
        require __DIR__ . '/../views/usuarios/index.php';
        require __DIR__ . '/../views/templates/footer.php';
    }

    /**
     * Exibe formulário e processa o cadastro de um novo usuário (Restrito ao perfil GESTOR)
     */
    public static function create(): void {
        AuthHelper::requirePerfil('GESTOR');

        $erro = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CsrfHelper::validate($_POST['csrf_token'] ?? null)) {
                $erro = "Token CSRF inválido ou expirado. Tente novamente.";
            } else {
                $matricula = trim($_POST['matricula'] ?? '');
                $nomeCompleto = trim($_POST['nome_completo'] ?? '');
                $perfil = trim($_POST['perfil'] ?? 'OPERADOR');
                $senha = $_POST['senha'] ?? '';

                if (empty($matricula) || empty($nomeCompleto) || empty($senha)) {
                    $erro = "Por favor, preencha a Matrícula, Nome Completo e Senha.";
                } elseif (strlen($senha) < 6) {
                    $erro = "A senha deve conter no mínimo 6 caracteres.";
                } elseif (UsuarioModel::findByMatricula($matricula) !== null) {
                    $erro = "A matrícula '{$matricula}' já está cadastrada para outro usuário.";
                } else {
                    UsuarioModel::create([
                        'matricula' => $matricula,
                        'nome_completo' => $nomeCompleto,
                        'perfil' => $perfil,
                        'senha' => $senha,
                        'created_by' => $_SESSION['usuario_id'] ?? null
                    ]);

                    $_SESSION['flash_sucesso'] = "Usuário '{$matricula}' cadastrado com sucesso!";
                    $config = require __DIR__ . '/../../config/config.php';
                    header('Location: ' . $config['app']['base_url'] . '/usuarios');
                    exit;
                }
            }
        }

        $config = require __DIR__ . '/../../config/config.php';
        $baseUrl = $config['app']['base_url'];

        $title = "Novo Usuário - WMS Agiliza";
        $currentRoute = "/usuarios";
        $usuario = null; // Modo criação

        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/templates/sidebar.php';
        require __DIR__ . '/../views/usuarios/form.php';
        require __DIR__ . '/../views/templates/footer.php';
    }

    /**
     * Exibe formulário e processa a edição de um usuário (Restrito ao perfil GESTOR)
     */
    public static function edit(): void {
        AuthHelper::requirePerfil('GESTOR');

        $id = (int)($_GET['id'] ?? 0);
        $usuario = UsuarioModel::findById($id);

        if (!$usuario) {
            http_response_code(404);
            echo "<h1>404 - Usuário Não Encontrado</h1>";
            exit;
        }

        $erro = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CsrfHelper::validate($_POST['csrf_token'] ?? null)) {
                $erro = "Token CSRF inválido ou expirado. Tente novamente.";
            } else {
                $matricula = trim($_POST['matricula'] ?? '');
                $nomeCompleto = trim($_POST['nome_completo'] ?? '');
                $perfil = trim($_POST['perfil'] ?? 'OPERADOR');
                $senha = $_POST['senha'] ?? ''; // Opcional ao editar

                if (empty($matricula) || empty($nomeCompleto)) {
                    $erro = "Por favor, preencha a Matrícula e o Nome Completo.";
                } elseif (!empty($senha) && strlen($senha) < 6) {
                    $erro = "A nova senha deve conter no mínimo 6 caracteres.";
                } elseif (UsuarioModel::findByMatricula($matricula, $id) !== null) {
                    $erro = "A matrícula '{$matricula}' já pertence a outro usuário.";
                } else {
                    UsuarioModel::update($id, [
                        'matricula' => $matricula,
                        'nome_completo' => $nomeCompleto,
                        'perfil' => $perfil,
                        'senha' => $senha,
                        'updated_by' => $_SESSION['usuario_id'] ?? null
                    ]);

                    $_SESSION['flash_sucesso'] = "Usuário '{$matricula}' atualizado com sucesso!";
                    $config = require __DIR__ . '/../../config/config.php';
                    header('Location: ' . $config['app']['base_url'] . '/usuarios');
                    exit;
                }
            }
        }

        $config = require __DIR__ . '/../../config/config.php';
        $baseUrl = $config['app']['base_url'];

        $title = "Editar Usuário - WMS Agiliza";
        $currentRoute = "/usuarios";

        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/templates/sidebar.php';
        require __DIR__ . '/../views/usuarios/form.php';
        require __DIR__ . '/../views/templates/footer.php';
    }

    /**
     * Processa a exclusão lógica (Soft Delete) de um usuário
     */
    public static function delete(): void {
        AuthHelper::requirePerfil('GESTOR');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (CsrfHelper::validate($_POST['csrf_token'] ?? null)) {
                $id = (int)($_POST['id'] ?? 0);
                
                // Impede que o gestor exclua a si próprio
                if ($id === (int)($_SESSION['usuario_id'] ?? 0)) {
                    $_SESSION['flash_erro'] = "Você não pode excluir sua própria conta de usuário ativa.";
                } else {
                    $usuario = UsuarioModel::findById($id);
                    if ($usuario) {
                        UsuarioModel::softDelete($id);
                        $_SESSION['flash_sucesso'] = "Usuário '{$usuario['matricula']}' desativado com sucesso!";
                    }
                }
            } else {
                $_SESSION['flash_erro'] = "Token CSRF inválido ao tentar desativar o usuário.";
            }
        }

        $config = require __DIR__ . '/../../config/config.php';
        header('Location: ' . $config['app']['base_url'] . '/usuarios');
        exit;
    }
}
