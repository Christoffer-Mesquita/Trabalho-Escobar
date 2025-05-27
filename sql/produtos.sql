-- Tabela de produtos
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

-- Índices para melhorar a performance
CREATE INDEX idx_produtos_categoria ON produtos(categoria_id);
CREATE INDEX idx_produtos_nome ON produtos(nome);
CREATE INDEX idx_produtos_ativo ON produtos(ativo);

-- Se a tabela já existir, adicionar a coluna estoque se ela não existir
ALTER TABLE produtos
ADD COLUMN IF NOT EXISTS estoque INT NOT NULL DEFAULT 0 AFTER preco; 