<?php

require __DIR__ . '/sessao.php';
require __DIR__ . '/../banco/conexao.php';

class Cliente
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listar(string $busca): array
    {
        if ($busca === '') {
            return $this->pdo->query('SELECT * FROM clientes ORDER BY id DESC')->fetchAll();
        }

        $termo = '%' . $busca . '%';
        $consulta = $this->pdo->prepare(
            'SELECT * FROM clientes
             WHERE nome LIKE ? OR email LIKE ? OR telefone LIKE ? OR cidade LIKE ?
             ORDER BY id DESC'
        );
        $consulta->execute([$termo, $termo, $termo, $termo]);
        return $consulta->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        $consulta = $this->pdo->prepare('SELECT * FROM clientes WHERE id = ?');
        $consulta->execute([$id]);
        return $consulta->fetch() ?: null;
    }

    public function salvar(int $id, array $dados): int
    {
        $valores = [
            trim($dados['nome']),
            trim($dados['telefone']),
            trim($dados['email'] ?? ''),
            trim($dados['cep'] ?? ''),
            trim($dados['logradouro'] ?? ''),
            trim($dados['bairro'] ?? ''),
            trim($dados['cidade'] ?? ''),
            strtoupper(trim($dados['uf'] ?? '')),
        ];

        if ($id > 0) {
            $sql = 'UPDATE clientes SET nome = ?, telefone = ?, email = ?, cep = ?,
                    logradouro = ?, bairro = ?, cidade = ?, uf = ? WHERE id = ?';
            $valores[] = $id;
        } else {
            $sql = 'INSERT INTO clientes (nome, telefone, email, cep, logradouro, bairro, cidade, uf)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        }

        $consulta = $this->pdo->prepare($sql);
        $consulta->execute($valores);
        return $id > 0 ? $id : (int) $this->pdo->lastInsertId();
    }

    public function excluir(int $id): void
    {
        $consulta = $this->pdo->prepare('DELETE FROM clientes WHERE id = ?');
        $consulta->execute([$id]);
    }
}

// Pedidos da tela de clientes
header('Content-Type: application/json; charset=utf-8');
exigirLoginApi();

$acao = $_POST['acao'] ?? $_GET['acao'] ?? 'listar';
$id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

try {
    $clientes = new Cliente(conectarBanco());

    if ($acao === 'listar') {
        $busca = trim($_GET['busca'] ?? '');
        echo json_encode(['sucesso' => true, 'dados' => $clientes->listar($busca)]);
        exit;
    }

    if ($acao === 'buscar') {
        $cliente = $clientes->buscar($id);
        echo json_encode(['sucesso' => $cliente !== null, 'dados' => $cliente]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($acao === 'salvar') {
            if (trim($_POST['nome'] ?? '') === '' || trim($_POST['telefone'] ?? '') === '') {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Nome e telefone são obrigatórios.']);
                exit;
            }

            $idSalvo = $clientes->salvar($id, $_POST);
            $mensagem = $id > 0 ? 'Cliente atualizado com sucesso.' : 'Cliente cadastrado com sucesso.';
            echo json_encode(['sucesso' => true, 'id' => $idSalvo, 'mensagem' => $mensagem]);
            exit;
        }

        if ($acao === 'excluir') {
            if ($id <= 0) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'ID inválido.']);
                exit;
            }

            $clientes->excluir($id);
            echo json_encode(['sucesso' => true, 'mensagem' => 'Cliente excluído com sucesso.']);
            exit;
        }
    }

    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Ação inválida.']);
} catch (PDOException $erro) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não foi possível concluir a operação com clientes.']);
}
