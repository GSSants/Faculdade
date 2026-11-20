<?php

require_once __DIR__ . '/../../config/Database.php';

// Modelo Imovel

class Imovel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    // Listar imoveis com filtros
    public function listar(string $busca = '', string $status = ''): array
    {
        $sql = 'SELECT i.*, c.nome AS cliente_nome
                FROM imoveis i
                LEFT JOIN clientes c ON c.id = i.cliente_id
                WHERE 1=1';
        $params = [];

        if ($busca !== '') {
            $sql .= ' AND (i.titulo LIKE ? OR i.bairro LIKE ? OR i.cidade LIKE ? OR i.logradouro LIKE ?)';
            $termo = '%' . $busca . '%';
            array_push($params, $termo, $termo, $termo, $termo);
        }

        if ($status !== '') {
            $sql .= ' AND i.status = ?';
            $params[] = $status;
        }

        $sql .= ' ORDER BY i.id DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Buscar por ID
    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM imoveis WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // Inserir imovel
    public function inserir(array $dados, int $usuarioId): int
    {
        $clienteId = !empty($dados['cliente_id']) ? (int) $dados['cliente_id'] : null;

        $stmt = $this->pdo->prepare(
            'INSERT INTO imoveis
             (titulo, tipo, finalidade, valor, area_m2, cep, logradouro, bairro, cidade, uf, status, cliente_id, usuario_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            trim($dados['titulo']),
            trim($dados['tipo']),
            trim($dados['finalidade']),
            (float) $dados['valor'],
            (float) $dados['area_m2'],
            trim($dados['cep']),
            trim($dados['logradouro']),
            trim($dados['bairro']),
            trim($dados['cidade']),
            strtoupper(trim($dados['uf'])),
            trim($dados['status'] ?? 'Disponível'),
            $clienteId,
            $usuarioId,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    // Atualizar imovel
    public function atualizar(int $id, array $dados): bool
    {
        $clienteId = !empty($dados['cliente_id']) ? (int) $dados['cliente_id'] : null;

        $stmt = $this->pdo->prepare(
            'UPDATE imoveis
             SET titulo = ?, tipo = ?, finalidade = ?, valor = ?, area_m2 = ?,
                 cep = ?, logradouro = ?, bairro = ?, cidade = ?, uf = ?, status = ?, cliente_id = ?
             WHERE id = ?'
        );
        return $stmt->execute([
            trim($dados['titulo']),
            trim($dados['tipo']),
            trim($dados['finalidade']),
            (float) $dados['valor'],
            (float) $dados['area_m2'],
            trim($dados['cep']),
            trim($dados['logradouro']),
            trim($dados['bairro']),
            trim($dados['cidade']),
            strtoupper(trim($dados['uf'])),
            trim($dados['status'] ?? 'Disponível'),
            $clienteId,
            $id,
        ]);
    }

    // Excluir imovel
    public function excluir(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM imoveis WHERE id = ?');
        return $stmt->execute([$id]);
    }

    // Totais do painel
    public function resumo(): array
    {
        $totalImoveis = (int) $this->pdo->query('SELECT COUNT(*) FROM imoveis')->fetchColumn();
        $disponiveis = (int) $this->pdo->query("SELECT COUNT(*) FROM imoveis WHERE status = 'Disponível'")->fetchColumn();
        $totalClientes = (int) $this->pdo->query('SELECT COUNT(*) FROM clientes')->fetchColumn();

        return [
            'total_imoveis' => $totalImoveis,
            'disponiveis' => $disponiveis,
            'total_clientes' => $totalClientes,
        ];
    }
}
