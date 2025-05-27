-- Create database if not exists
CREATE DATABASE IF NOT EXISTS loja_virtual;
USE loja_virtual;

-- Create categorias table (primeira tabela pois não tem dependências)
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create usuarios table (segunda tabela pois produtos e pedidos dependem dela)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    cep VARCHAR(9),
    endereco VARCHAR(200),
    numero VARCHAR(10),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado CHAR(2),
    is_admin BOOLEAN DEFAULT FALSE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_login TIMESTAMP NULL,
    ativo BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create produtos table (depende de categorias)
CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    estoque INT NOT NULL DEFAULT 0,
    categoria_id INT NOT NULL,
    imagem VARCHAR(255),
    data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create pedidos table (depende de usuarios)
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    data_pedido DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pendente', 'aprovado', 'enviado', 'entregue', 'cancelado') NOT NULL DEFAULT 'pendente',
    total DECIMAL(10,2) NOT NULL,
    endereco_entrega TEXT NOT NULL,
    observacoes TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create pedidos_itens table (depende de pedidos e produtos)
CREATE TABLE IF NOT EXISTS pedidos_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create indexes for better performance
CREATE INDEX idx_produtos_categoria ON produtos(categoria_id);
CREATE INDEX idx_produtos_nome ON produtos(nome);
CREATE INDEX idx_produtos_ativo ON produtos(ativo);

CREATE INDEX idx_pedidos_usuario ON pedidos(usuario_id);
CREATE INDEX idx_pedidos_status ON pedidos(status);
CREATE INDEX idx_pedidos_data ON pedidos(data_pedido);
CREATE INDEX idx_pedidos_itens_pedido ON pedidos_itens(pedido_id);
CREATE INDEX idx_pedidos_itens_produto ON pedidos_itens(produto_id);

-- Insert initial data
INSERT INTO categorias (nome) VALUES 
('Roupas'), 
('Calçados'), 
('Acessórios');

-- Insert sample products
INSERT INTO produtos (nome, descricao, preco, estoque, categoria_id, ativo) VALUES
('Camiseta Básica', 'Camiseta 100% algodão, disponível em várias cores', 49.90, 50, 1, TRUE),
('Tênis Esportivo', 'Tênis confortável para corrida e caminhada', 199.90, 30, 2, TRUE),
('Boné Estiloso', 'Boné ajustável com proteção UV', 39.90, 25, 3, TRUE);

-- Insert admin user (password: admin123)
INSERT INTO usuarios (nome, email, senha, is_admin, ativo) VALUES
('Administrador', 'admin@loja.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE, TRUE); 