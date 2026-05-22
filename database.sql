-- SmartStock Inventory Management System Database
-- Create and use the database
CREATE DATABASE IF NOT EXISTS smartstock CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smartstock;

-- ============================================================
-- USERS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- CATEGORIES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- PRODUCTS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    critical_limit INT NOT NULL DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- STOCK MOVEMENTS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS stock_movements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    movement_type ENUM('in','out') NOT NULL,
    quantity INT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- ============================================================
-- Default admin account: admin / Admin@1234
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@smartstock.com', '$2y$10$7rjChYxoolXK.5L2qgReA.dQbK3KXaK/qyEBHBImn4TG9Si9YS./G', 'admin'),
('john', 'john@smartstock.com', '$2y$10$7rjChYxoolXK.5L2qgReA.dQbK3KXaK/qyEBHBImn4TG9Si9YS./G', 'user');
-- Note: both default passwords are "password" – change in production!

INSERT INTO categories (name, description) VALUES
('Electronics', 'Electronic devices and components'),
('Office Supplies', 'Stationery and office equipment'),
('Furniture', 'Office and warehouse furniture'),
('Cleaning', 'Cleaning products and equipment');

INSERT INTO products (name, category_id, quantity, price, critical_limit) VALUES
('Laptop Dell XPS', 1, 15, 1299.99, 3),
('Wireless Mouse', 1, 50, 29.99, 10),
('USB-C Hub', 1, 8, 49.99, 5),
('Ballpoint Pens (Box)', 2, 3, 12.99, 10),
('A4 Paper Ream', 2, 100, 5.49, 20),
('Office Chair', 3, 4, 249.99, 2),
('Standing Desk', 3, 2, 599.99, 1),
('Floor Cleaner 5L', 4, 6, 18.99, 5),
('Vacuum Cleaner', 4, 3, 189.99, 2),
('HDMI Cable 2m', 1, 25, 14.99, 8);
