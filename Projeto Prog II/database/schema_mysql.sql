-- Banco de dados MySQL

CREATE DATABASE IF NOT EXISTS imobiliaria_prog2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE imobiliaria_prog2;

-- Tabela usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela clientes
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    telefone VARCHAR(30) NOT NULL,
    email VARCHAR(150),
    cep VARCHAR(15),
    logradouro VARCHAR(180),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    uf CHAR(2),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela imoveis
CREATE TABLE IF NOT EXISTS imoveis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    finalidade VARCHAR(30) NOT NULL,
    valor DECIMAL(12, 2) NOT NULL,
    area_m2 DECIMAL(10, 2) NOT NULL,
    cep VARCHAR(15) NOT NULL,
    logradouro VARCHAR(180) NOT NULL,
    bairro VARCHAR(100) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    uf CHAR(2) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Disponível',
    cliente_id INT NULL,
    usuario_id INT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_imoveis_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    CONSTRAINT fk_imoveis_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);
