DROP DATABASE nenufar_bank;
CREATE DATABASE nenufar_bank;
USE nenufar_bank;

CREATE TABLE usuarios (
    documento   VARCHAR(20) PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL,
    email       VARCHAR(100) UNIQUE NOT NULL,
    password    VARCHAR(255) NOT NULL,
    saldo       DECIMAL(10,2) DEFAULT 0.00, 
    tamalbits   INT DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categorias (
    id      INT PRIMARY KEY AUTO_INCREMENT,
    nombre  VARCHAR(100) NOT NULL
);

CREATE TABLE productos (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    imagen       VARCHAR(200),
    nombre       VARCHAR(100) NOT NULL,
    descripcion  VARCHAR(100),
    precio       DECIMAL(10,2) NOT NULL,
    id_categoria INT,
    activo       BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id)
);

CREATE TABLE gastos (
    id                INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario        VARCHAR(20) NOT NULL,
    id_producto       INT NOT NULL,
    monto             DECIMAL(10,2) NOT NULL,
    descripcion       VARCHAR(100),
    fecha             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tamalbits_ganados INT DEFAULT 0,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(documento),
    FOREIGN KEY (id_producto) REFERENCES productos(id)
);

-- Usuario con saldo inicial
INSERT INTO usuarios (documento, nombre, email, password, saldo, tamalbits)
VALUES ('1094899647', 'Nicol Ocampo', 'nicol@example.com', '123456', 500000.00, 150);

-- Categorías que el frontend espera
INSERT INTO categorias (nombre) VALUES
('Comida'),
('Tecnologia'),
('Hogar'),
('Ropa');

-- Productos con categorías correctas (sin / inicial en imagen)
INSERT INTO productos (imagen, nombre, descripcion, precio, id_categoria, activo)
VALUES
('images/Hamburguesa.jpg',   'Hamburguesa',          'Hamburguesa doble carne',      18000, 1, TRUE),
('images/audifonos.jpg',     'Audifonos Bluetooth',  'Audifonos inalambricos',       95000, 2, TRUE),
('images/lampara.jpg',       'Lampara LED',          'Lampara para escritorio',      45000, 3, TRUE),
('images/camisa_negra.jpg',  'Camiseta Negra',       'Camiseta algodón unisex',      35000, 4, TRUE);