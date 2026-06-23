-- SHOEPEE Database Schema
-- Database: shoepee_db

CREATE DATABASE IF NOT EXISTS shoepee_db;
USE shoepee_db;

-- Users table
CREATE TABLE tbl_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(20) NOT NULL,
    email VARCHAR(30) NOT NULL,
    password VARCHAR(255) NOT NULL,
    access VARCHAR(10) NOT NULL DEFAULT 'user',
    account_status VARCHAR(10) NOT NULL DEFAULT 'active',
    profile_img VARCHAR(255) DEFAULT NULL
);

-- Admins table
CREATE TABLE tbl_admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_img VARCHAR(255) DEFAULT NULL
);

-- Products table
CREATE TABLE tbl_products (
    prod_id INT AUTO_INCREMENT PRIMARY KEY,
    brand VARCHAR(50) NOT NULL,
    model_name VARCHAR(50) NOT NULL,
    size_8 TINYINT(1) DEFAULT 0,
    size_85 TINYINT(1) DEFAULT 0,
    size_9 TINYINT(1) DEFAULT 0,
    size_95 TINYINT(1) DEFAULT 0,
    size_10 TINYINT(1) DEFAULT 0,
    size_105 TINYINT(1) DEFAULT 0,
    size_11 TINYINT(1) DEFAULT 0,
    size_115 TINYINT(1) DEFAULT 0,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    description TEXT,
    img_url VARCHAR(255),
    product_archive VARCHAR(10) DEFAULT 'FALSE',
    prod_status VARCHAR(20) DEFAULT 'Just In'
);

-- Shopping bag table
CREATE TABLE tbl_bag (
    bag_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    prod_id INT NOT NULL,
    selected_size VARCHAR(10),
    FOREIGN KEY (user_id) REFERENCES tbl_users(id),
    FOREIGN KEY (prod_id) REFERENCES tbl_products(prod_id)
);

-- Favorites table
CREATE TABLE tbl_favorites (
    fav_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    prod_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES tbl_users(id),
    FOREIGN KEY (prod_id) REFERENCES tbl_products(prod_id)
);

-- Checkout history table
CREATE TABLE tbl_checkout_history (
    checkout_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    prod_id INT NOT NULL,
    selected_size VARCHAR(10),
    quantity INT DEFAULT 1,
    history_archive VARCHAR(10) DEFAULT 'FALSE',
    checkout_datetime DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES tbl_users(id),
    FOREIGN KEY (prod_id) REFERENCES tbl_products(prod_id)
);

-- Order history table
CREATE TABLE tbl_order_history (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_date DATETIME NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_fee DECIMAL(10,2) DEFAULT 0,
    history_archive VARCHAR(10) DEFAULT 'FALSE',
    FOREIGN KEY (user_id) REFERENCES tbl_users(id)
);

-- Order items table
CREATE TABLE tbl_order_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    prod_id INT NOT NULL,
    model_name VARCHAR(50) NOT NULL,
    quantity INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES tbl_order_history(order_id),
    FOREIGN KEY (prod_id) REFERENCES tbl_products(prod_id)
);
