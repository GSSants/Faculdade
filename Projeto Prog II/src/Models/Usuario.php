<?php

require_once __DIR__ . '/../../config/Database.php';

// Modelo Usuario

class Usuario
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    // Buscar por e-mail
    public function buscarPorEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, nome, email, senha_hash FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // Autenticar credenciais
    public function autenticar(string $email, string $senha): ?array
    {
        $usuario = $this->buscarPorEmail($email);
        if (!$usuario || !password_verify($senha, $usuario['senha_hash'])) {
            return null;
        }
        unset($usuario['senha_hash']);
        return $usuario;
    }

    // Cadastrar novo usuario
    public function criar(string $nome, string $email, string $senha): int
    {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare('INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)');
        $stmt->execute([$nome, $email, $hash]);
        return (int) $this->pdo->lastInsertId();
    }
}
