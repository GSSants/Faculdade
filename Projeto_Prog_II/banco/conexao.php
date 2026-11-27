<?php

function conectarBanco(): PDO
{
    $banco = 'sqlite';
    $opcoes = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    if ($banco === 'mysql') {
        return new PDO(
            'mysql:host=127.0.0.1;dbname=imobiliaria_prog2;charset=utf8mb4',
            'root',
            '',
            $opcoes
        );
    }

    $pdo = new PDO('sqlite:' . __DIR__ . '/imobiliaria.sqlite', null, null, $opcoes);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA busy_timeout = 5000');

    $consulta = "SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'usuarios'";
    if (!$pdo->query($consulta)->fetchColumn()) {
        // A transação impede que dois acessos criem os dados iniciais ao mesmo tempo.
        $pdo->exec('BEGIN IMMEDIATE');

        try {
            if (!$pdo->query($consulta)->fetchColumn()) {
                $pdo->exec(file_get_contents(__DIR__ . '/schema.sql'));
                inserirDadosIniciais($pdo);
            }
            $pdo->exec('COMMIT');
        } catch (Throwable $erro) {
            $pdo->exec('ROLLBACK');
            throw $erro;
        }
    }

    return $pdo;
}

function inserirDadosIniciais(PDO $pdo): void
{
    $usuario = $pdo->prepare('INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)');
    $usuario->execute(['Gabriel Soares', 'admin@imobiliaria.com', password_hash('123456', PASSWORD_DEFAULT)]);
    $usuarioId = (int) $pdo->lastInsertId();

    $cliente = $pdo->prepare(
        'INSERT INTO clientes (nome, telefone, email, cep, logradouro, bairro, cidade, uf)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $clientes = [
        ['Carlos Eduardo Lima', '(31) 99812-3401', 'carlos.lima@email.com', '30140-071', 'Rua da Bahia', 'Centro', 'Belo Horizonte', 'MG'],
        ['Mariana Alves Souza', '(11) 98745-2210', 'mariana.souza@email.com', '01310-100', 'Avenida Paulista', 'Bela Vista', 'São Paulo', 'SP'],
        ['Roberto Ferreira', '(31) 99104-8820', 'roberto.f@email.com', '30130-110', 'Avenida Afonso Pena', 'Centro', 'Belo Horizonte', 'MG'],
    ];

    $idsClientes = [];
    foreach ($clientes as $dados) {
        $cliente->execute($dados);
        $idsClientes[] = (int) $pdo->lastInsertId();
    }

    $imovel = $pdo->prepare(
        'INSERT INTO imoveis
         (titulo, tipo, finalidade, valor, area_m2, cep, logradouro, bairro, cidade, uf, status, cliente_id, usuario_id)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $imovel->execute([
        'Apartamento 2 Quartos - Savassi', 'Apartamento', 'Venda', 485000, 72.5,
        '30112-010', 'Rua Pernambuco', 'Savassi', 'Belo Horizonte', 'MG', 'Disponível', $idsClientes[0], $usuarioId,
    ]);
    $imovel->execute([
        'Sala Comercial - Av. Paulista', 'Comercial', 'Aluguel', 4200, 55,
        '01310-100', 'Avenida Paulista', 'Bela Vista', 'São Paulo', 'SP', 'Disponível', $idsClientes[1], $usuarioId,
    ]);
    $imovel->execute([
        'Casa 3 Quartos com Quintal', 'Casa', 'Venda', 690000, 165,
        '30310-370', 'Rua Pium-í', 'Anchieta', 'Belo Horizonte', 'MG', 'Alugado', $idsClientes[2], $usuarioId,
    ]);
}
