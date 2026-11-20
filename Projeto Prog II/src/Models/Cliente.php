<?php

require_once __DIR__ . '/../../config/Database.php';

// Modelo Cliente

class Cliente
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    // Listar clientes
    public function listar(string $busca = ''): array
    {
        if ($busca !== '') {
            $termo = '%' . $busca . '%';
            $stmt = $this->pdo->prepare(
                'SELECT * FROM clientes
                 WHERE nome LIKE ? OR email LIKE ? OR telefone LIKE ? OR cidade LIKE ?
                 ORDER BY id DESC'
            );
            $stmt->execute([$termo, $termo, $termo, $termo]);
            return $stmt->fetchAll();
        }

        $stmt = $this->pdo->query('SELECT * FROM clientes ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    // Buscar por ID
    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM clientes WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // Inserir cliente
    public function inserir(array $dados): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO clientes (nome, telefone, email, cep, logradouro, bairro, cidade, uf)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            trim($dados['nome']),
            trim($dados['telefone']),
            trim($dados['email'] ?? ''),
            trim($dados['cep'] ?? ''),
            trim($dados['logradouro'] ?? ''),
            trim($dados['bairro'] ?? ''),
            trim($dados['cidade'] ?? ''),
            strtoupper(trim($dados['uf'] ?? '')),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    // Atualizar cliente
    public function atualizar(int $id, array $dados): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE clientes
             SET nome = ?, telefone = ?, email = ?, cep = ?, logradouro = ?, bairro = ?, cidade = ?, uf = ?
             WHERE id = ?'
        );
        return $stmt->execute([
            trim($dados['nome']),
            trim($dados['telefone']),
            trim($dados['email'] ?? ''),
            trim($dados['cep'] ?? ''),
            trim($dados['logradouro'] ?? ''),
            trim($dados['bairro'] ?? ''),
            trim($dados['cidade'] ?? ''),
            strtoupper(trim($dados['uf'] ?? '')),
            $id,
        ]);
    }

    // Excluir cliente
    public function excluir(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM clientes WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
