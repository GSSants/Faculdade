<?php

require_once __DIR__ . '/../Models/Usuario.php';

// Controlador de Autenticação

class AuthController
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->usuarioModel = new Usuario();
    }

    // Verificar usuário logado
    public static function verificarSessao(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['usuario'] ?? null;
    }

    // Exigir autenticação na API
    public static function exigirLoginApi(): array
    {
        $usuario = self::verificarSessao();
        if (!$usuario) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Sessão expirada. Faça login novamente.']);
            exit;
        }
        return $usuario;
    }

    // Processar requisições da API de autenticação
    public function handle(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $acao = $_GET['acao'] ?? $_POST['acao'] ?? '';

        if ($acao === 'login') {
            $email = trim($_POST['email'] ?? '');
            $senha = $_POST['senha'] ?? '';

            if ($email === '' || $senha === '') {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha e-mail e senha.']);
                return;
            }

            $usuario = $this->usuarioModel->autenticar($email, $senha);
            if (!$usuario) {
                http_response_code(401);
                echo json_encode(['sucesso' => false, 'mensagem' => 'E-mail ou senha inválidos.']);
                return;
            }

            $_SESSION['usuario'] = $usuario;
            echo json_encode(['sucesso' => true, 'usuario' => $usuario]);
            return;
        }

        if ($acao === 'registrar') {
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $senha = $_POST['senha'] ?? '';

            if ($nome === '' || $email === '' || strlen($senha) < 4) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Informe nome, e-mail e senha (mín. 4 caracteres).']);
                return;
            }

            if ($this->usuarioModel->buscarPorEmail($email)) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'E-mail já cadastrado.']);
                return;
            }

            $id = $this->usuarioModel->criar($nome, $email, $senha);
            $_SESSION['usuario'] = ['id' => $id, 'nome' => $nome, 'email' => $email];
            echo json_encode(['sucesso' => true, 'usuario' => $_SESSION['usuario']]);
            return;
        }

        if ($acao === 'logout') {
            session_destroy();
            echo json_encode(['sucesso' => true]);
            return;
        }

        if ($acao === 'status') {
            $usuario = self::verificarSessao();
            echo json_encode(['autenticado' => $usuario !== null, 'usuario' => $usuario]);
            return;
        }

        http_response_code(400);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Ação inválida.']);
    }
}
