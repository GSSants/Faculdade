<?php

// Conexao com o banco de dados

class Database
{
    private static ?PDO $instance = null;
    private static string $driver = 'sqlite';

    // Configuracao MySQL opcional
    private static string $mysqlHost = '127.0.0.1';
    private static string $mysqlDb = 'imobiliaria_prog2';
    private static string $mysqlUser = 'root';
    private static string $mysqlPass = '';

    public static function getConnection(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        if (self::$driver === 'mysql') {
            $dsn = "mysql:host=" . self::$mysqlHost . ";dbname=" . self::$mysqlDb . ";charset=utf8mb4";
            $pdo = new PDO($dsn, self::$mysqlUser, self::$mysqlPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            self::$instance = $pdo;
            return self::$instance;
        }

        // Conexao SQLite
        $dbPath = __DIR__ . '/../database/imobiliaria.sqlite';
        $novoBanco = !file_exists($dbPath);

        $pdo = new PDO('sqlite:' . $dbPath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON;');

        self::$instance = $pdo;

        if ($novoBanco) {
            self::inicializarBanco($pdo);
        }

        return self::$instance;
    }

    // Criacao das tabelas e dados iniciais
    private static function inicializarBanco(PDO $pdo): void
    {
        $schemaPath = __DIR__ . '/../database/schema.sql';
        if (file_exists($schemaPath)) {
            $sql = file_get_contents($schemaPath);
            $pdo->exec($sql);
        }

        // Usuario padrao
        $senhaHash = password_hash('123456', PASSWORD_DEFAULT);
        $stmtUser = $pdo->prepare('INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)');
        $stmtUser->execute(['Gabriel Soares', 'admin@imobiliaria.com', $senhaHash]);
        $usuarioId = (int) $pdo->lastInsertId();

        // Clientes iniciais
        $stmtCliente = $pdo->prepare(
            'INSERT INTO clientes (nome, telefone, email, cep, logradouro, bairro, cidade, uf) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmtCliente->execute(['Carlos Eduardo Lima', '(31) 99812-3401', 'carlos.lima@email.com', '30140-071', 'Rua da Bahia', 'Centro', 'Belo Horizonte', 'MG']);
        $c1 = (int) $pdo->lastInsertId();

        $stmtCliente->execute(['Mariana Alves Souza', '(11) 98745-2210', 'mariana.souza@email.com', '01310-100', 'Avenida Paulista', 'Bela Vista', 'São Paulo', 'SP']);
        $c2 = (int) $pdo->lastInsertId();

        $stmtCliente->execute(['Roberto Ferreira', '(31) 99104-8820', 'roberto.f@email.com', '30130-110', 'Avenida Afonso Pena', 'Centro', 'Belo Horizonte', 'MG']);
        $c3 = (int) $pdo->lastInsertId();

        // Imóveis iniciais
        $stmtImovel = $pdo->prepare(
            'INSERT INTO imoveis (titulo, tipo, finalidade, valor, area_m2, cep, logradouro, bairro, cidade, uf, status, cliente_id, usuario_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $stmtImovel->execute([
            'Apartamento 2 Quartos - Savassi', 'Apartamento', 'Venda', 485000.00, 72.5,
            '30112-010', 'Rua Pernambuco', 'Savassi', 'Belo Horizonte', 'MG', 'Disponível', $c1, $usuarioId
        ]);
        $stmtImovel->execute([
            'Sala Comercial - Av. Paulista', 'Comercial', 'Aluguel', 4200.00, 55.0,
            '01310-100', 'Avenida Paulista', 'Bela Vista', 'São Paulo', 'SP', 'Disponível', $c2, $usuarioId
        ]);
        $stmtImovel->execute([
            'Casa 3 Quartos com Quintal', 'Casa', 'Venda', 690000.00, 165.0,
            '30310-370', 'Rua Pium-í', 'Anchieta', 'Belo Horizonte', 'MG', 'Alugado', $c3, $usuarioId
        ]);
    }
}
