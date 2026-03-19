--
-- SQL Injection & Web Security Lab Setup for MySQL/MariaDB
-- Spring 2026 — Information Security Assignment 03
--

-- 1. Create the database
CREATE DATABASE IF NOT EXISTS assignment_db;
USE assignment_db;

-- ============================================================
-- 2. USERS TABLE (for SQL Injection tasks)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password_hash VARCHAR(60) NOT NULL,
    role VARCHAR(20) NOT NULL,
    secret_flag VARCHAR(100)
);

-- All passwords are 'password', MD5 hash is 5f4dcc3b5aa765d61d8327deb882cf99
INSERT INTO users (username, password_hash, role, secret_flag) VALUES 
('alice', '5f4dcc3b5aa765d61d8327deb882cf99', 'user', 'USER_FLAG_ABC123'),
('bob', '5f4dcc3b5aa765d61d8327deb882cf99', 'user', 'USER_FLAG_DEF456'),
('charlie', '5f4dcc3b5aa765d61d8327deb882cf99', 'support', 'SUPPORT_FLAG_GHI789'),
('dave', '5f4dcc3b5aa765d61d8327deb882cf99', 'user', 'USER_FLAG_JKL012'),
('eve', '5f4dcc3b5aa765d61d8327deb882cf99', 'premium', 'PREMIUM_FLAG_MNO345'),
('mallory', '5f4dcc3b5aa765d61d8327deb882cf99', 'user', 'USER_FLAG_PQR678'),
('spongebob', '5f4dcc3b5aa765d61d8327deb882cf99', 'user', 'USER_FLAG_KRP101'),
('patrick', '5f4dcc3b5aa765d61d8327deb882cf99', 'user', 'USER_FLAG_KRP102'),
('squidward', '5f4dcc3b5aa765d61d8327deb882cf99', 'support', 'SUPPORT_FLAG_KRP103'),
('homer', '5f4dcc3b5aa765d61d8327deb882cf99', 'user', 'USER_FLAG_DOH999'),
('bart', '5f4dcc3b5aa765d61d8327deb882cf99', 'premium', 'PREMIUM_FLAG_BRT888'),
('batman', '5f4dcc3b5aa765d61d8327deb882cf99', 'admin', 'FLAG_SQLI_GOTHAM_000'),
('admin', '5f4dcc3b5aa765d61d8327deb882cf99', 'admin', 'FLAG_SQLI_COMPLETE_ZXY789');

-- ============================================================
-- 3. PRODUCTS TABLE (for Union-Based SQLi tasks)
-- ============================================================
CREATE TABLE IF NOT EXISTS products (
    product_id INT PRIMARY KEY,
    name VARCHAR(100),
    price DECIMAL(10, 2)
);
INSERT INTO products (product_id, name, price) VALUES 
(101, 'Secure Firewall', 999.00),
(102, 'Encrypted Monitor', 499.00),
(103, 'Zero-Day Scanner', 1299.00),
(104, 'Threat Intelligence Feed', 749.00),
(105, 'Cloud WAF Subscription', 149.99),
(106, 'Endpoint Detection & Response (EDR)', 899.50),
(107, 'Hardware Security Module (HSM)', 4500.00),
(108, 'Identity Access Management License', 299.00),
(109, 'Penetration Testing Toolkit', 1999.00),
(110, 'Acme Corp Anvil (Cloud Edition)', 99.99),
(111, 'Krabby Patty Secret Formula (Encrypted)', 1000000.00),
(112, 'Bat-Signal Network Scanner', 5500.00),
(113, 'Stark Industries Arc Reactor Shield', 99999.99),
(114, 'Duff Beer API Key (Lifetime)', 24.99),
(115, 'Portal Gun (Aperture Science)', 7499.00);

-- ============================================================
-- 4. GUESTBOOK TABLE (for Stored XSS tasks)
-- ============================================================
CREATE TABLE IF NOT EXISTS guestbook (
    id INT AUTO_INCREMENT PRIMARY KEY,
    author VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO guestbook (author, message) VALUES
('Alice', 'Welcome to the security lab! Great resources here.'),
('Bob', 'Just finished the SQL injection module. Very informative!'),
('Charlie', 'Looking forward to the XSS challenges.'),
('Dave', 'Does anyone know the admin password? Asking for a friend.'),
('Eve', 'I think I found a bug in the products page! The URL looks weird sometimes.'),
('Mallory', 'This guestbook looks like a fun place to test some scripts.'),
('SupportTeam', 'Reminder: Do not share challenge flags in the guestbook!'),
('SpongeBob', 'I AM READY! I AM READY! (For the SQL injection exam!)'),
('Patrick', 'Is mayonnaise an SQL injection payload?'),
('Squidward', 'Can everyone please stop shouting in the guestbook? I am trying to focus.'),
('Homer', 'Mmm... database tables... *drool*'),
('Bart', 'Eat my shorts, firewall!'),
('Batman', 'I am vengeance. I am the night. I am securing this database.');

-- ============================================================
-- 5. PROFILES TABLE (for CSRF tasks)
-- ============================================================
CREATE TABLE IF NOT EXISTS profiles (
    user_id INT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    display_name VARCHAR(100) NOT NULL
);

INSERT INTO profiles (user_id, username, email, display_name) VALUES
(1, 'alice', 'alice@securecorp.local', 'Alice Johnson'),
(2, 'bob', 'bob@securecorp.local', 'Bob Smith'),
(3, 'admin', 'admin@securecorp.local', 'Administrator'),
(4, 'charlie', 'charlie@securecorp.local', 'Charlie Davis'),
(5, 'dave', 'dave@securecorp.local', 'David Wilson'),
(6, 'eve', 'eve@securecorp.local', 'Eve Hacker'),
(7, 'mallory', 'mallory@securecorp.local', 'Mallory Malice'),
(8, 'spongebob', 'spongebob@bikini-bottom.local', 'SpongeBob SquarePants'),
(9, 'patrick', 'patrick@bikini-bottom.local', 'Patrick Star'),
(10, 'squidward', 'squidward@bikini-bottom.local', 'Squidward Tentacles'),
(11, 'homer', 'homer@springfield.local', 'Homer Simpson'),
(12, 'bart', 'bart@springfield.local', 'Bart Simpson'),
(13, 'batman', 'bruce@wayne-enterprises.local', 'Bruce Wayne');

-- Note: Students should run this script in phpMyAdmin or MySQL client.
