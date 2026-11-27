<?php

require __DIR__ . '/sessao.php';
require __DIR__ . '/../banco/conexao.php';

class Usuario
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function buscarPorEmail(string $email): ?array
    {
        $consulta = $this->pdo->prepare('SELECT id, nome, email, senha_hash FROM usuarios WHERE email = ?');
        $consulta->execute([$email]);
        return $consulta->fetch() ?: null;
    }

    public function cadastrar(string $nome, string $email, string $senha): int
    {
        $consulta = $this->pdo->prepare('INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)');
        $consulta->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT)]);
        return (int) $this->pdo->lastInsertId();
    }
}

header('Content-Type: application/json; charset=utf-8');
$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

if ($acao === 'status') {
    $usuario = $_SESSION['usuario'] ?? null;
    echo json_encode(['autenticado' => $usuario !== null, 'usuario' => $usuario]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !in_array($acao, ['login', 'registrar', 'logout'], true)) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Ação inválida.']);
    exit;
}

if ($acao === 'logout') {
    $_SESSION = [];
    session_destroy();
    echo json_encode(['sucesso' => true]);
    exit;
}

try {
    $usuarios = new Usuario(conectarBanco());
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($acao === 'login') {
        if ($email === '' || $senha === '') {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha e-mail e senha.']);
            exit;
        }

        $usuario = $usuarios->buscarPorEmail($email);
        if (!$usuario || !password_verify($senha, $usuario['senha_hash'])) {
            http_response_code(401);
            echo json_encode(['sucesso' => false, 'mensagem' => 'E-mail ou senha inválidos.']);
            exit;
        }
        unset($usuario['senha_hash']);
    } else {
        $nome = trim($_POST['nome'] ?? '');
        if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 4) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Informe nome, e-mail válido e senha (mín. 4 caracteres).']);
            exit;
        }

        if ($usuarios->buscarPorEmail($email)) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'E-mail já cadastrado.']);
            exit;
        }

        $id = $usuarios->cadastrar($nome, $email, $senha);
        $usuario = ['id' => $id, 'nome' => $nome, 'email' => $email];
    }

    session_regenerate_id(true);
    $_SESSION['usuario'] = $usuario;
    echo json_encode(['sucesso' => true, 'usuario' => $usuario]);
} catch (PDOException $erro) {
    if ($acao === 'registrar' && $erro->getCode() === '23000') {
        echo json_encode(['sucesso' => false, 'mensagem' => 'E-mail já cadastrado.']);
        exit;
    }

    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não foi possível acessar o banco de dados.']);
}
