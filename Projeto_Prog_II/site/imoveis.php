<?php

require __DIR__ . '/sessao.php';
require __DIR__ . '/../banco/conexao.php';

class Imovel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listar(string $busca, string $status): array
    {
        $sql = 'SELECT i.*, c.nome AS cliente_nome
                FROM imoveis i
                LEFT JOIN clientes c ON c.id = i.cliente_id
                WHERE 1 = 1';
        $valores = [];

        if ($busca !== '') {
            $termo = '%' . $busca . '%';
            $sql .= ' AND (i.titulo LIKE ? OR i.bairro LIKE ? OR i.cidade LIKE ? OR i.logradouro LIKE ?)';
            $valores = [$termo, $termo, $termo, $termo];
        }

        if ($status !== '') {
            $sql .= ' AND i.status = ?';
            $valores[] = $status;
        }

        $consulta = $this->pdo->prepare($sql . ' ORDER BY i.id DESC');
        $consulta->execute($valores);
        return $consulta->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        $consulta = $this->pdo->prepare('SELECT * FROM imoveis WHERE id = ?');
        $consulta->execute([$id]);
        return $consulta->fetch() ?: null;
    }

    public function salvar(int $id, array $dados, int $usuarioId): int
    {
        $clienteId = empty($dados['cliente_id']) ? null : (int) $dados['cliente_id'];
        $valores = [
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
        ];

        if ($id > 0) {
            $sql = 'UPDATE imoveis SET titulo = ?, tipo = ?, finalidade = ?, valor = ?, area_m2 = ?,
                    cep = ?, logradouro = ?, bairro = ?, cidade = ?, uf = ?, status = ?, cliente_id = ?
                    WHERE id = ?';
            $valores[] = $id;
        } else {
            $sql = 'INSERT INTO imoveis
                    (titulo, tipo, finalidade, valor, area_m2, cep, logradouro, bairro, cidade, uf, status, cliente_id, usuario_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $valores[] = $usuarioId;
        }

        $consulta = $this->pdo->prepare($sql);
        $consulta->execute($valores);
        return $id > 0 ? $id : (int) $this->pdo->lastInsertId();
    }

    public function excluir(int $id): void
    {
        $consulta = $this->pdo->prepare('DELETE FROM imoveis WHERE id = ?');
        $consulta->execute([$id]);
    }

    public function resumo(): array
    {
        return [
            'total_imoveis' => (int) $this->pdo->query('SELECT COUNT(*) FROM imoveis')->fetchColumn(),
            'disponiveis' => (int) $this->pdo->query("SELECT COUNT(*) FROM imoveis WHERE status = 'Disponível'")->fetchColumn(),
            'total_clientes' => (int) $this->pdo->query('SELECT COUNT(*) FROM clientes')->fetchColumn(),
        ];
    }
}

// Pedidos da tela de imóveis
header('Content-Type: application/json; charset=utf-8');
$usuario = exigirLoginApi();

$acao = $_POST['acao'] ?? $_GET['acao'] ?? 'listar';
$id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

try {
    $imoveis = new Imovel(conectarBanco());

    if ($acao === 'listar') {
        $busca = trim($_GET['busca'] ?? '');
        $status = trim($_GET['status'] ?? '');
        echo json_encode([
            'sucesso' => true,
            'dados' => $imoveis->listar($busca, $status),
            'resumo' => $imoveis->resumo(),
        ]);
        exit;
    }

    if ($acao === 'buscar') {
        $imovel = $imoveis->buscar($id);
        echo json_encode(['sucesso' => $imovel !== null, 'dados' => $imovel]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($acao === 'salvar') {
            $obrigatorios = ['titulo', 'tipo', 'finalidade', 'valor', 'area_m2', 'cep', 'logradouro', 'bairro', 'cidade', 'uf'];
            foreach ($obrigatorios as $campo) {
                if (trim($_POST[$campo] ?? '') === '') {
                    echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos obrigatórios.']);
                    exit;
                }
            }

            $idSalvo = $imoveis->salvar($id, $_POST, (int) $usuario['id']);
            $mensagem = $id > 0 ? 'Imóvel atualizado com sucesso.' : 'Imóvel cadastrado com sucesso.';
            echo json_encode(['sucesso' => true, 'id' => $idSalvo, 'mensagem' => $mensagem]);
            exit;
        }

        if ($acao === 'excluir') {
            if ($id <= 0) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'ID inválido.']);
                exit;
            }

            $imoveis->excluir($id);
            echo json_encode(['sucesso' => true, 'mensagem' => 'Imóvel excluído com sucesso.']);
            exit;
        }
    }

    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Ação inválida.']);
} catch (PDOException $erro) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não foi possível concluir a operação com imóveis.']);
}
