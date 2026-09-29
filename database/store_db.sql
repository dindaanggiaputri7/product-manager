-- database/store_db.sql — Apotek Arcana
-- Import lewat phpMyAdmin (tab Import) atau: mysql -u root < database/store_db.sql

CREATE DATABASE IF NOT EXISTS store_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE store_db;

CREATE TABLE IF NOT EXISTS products (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100)  NOT NULL UNIQUE,
  category   VARCHAR(50)   NOT NULL DEFAULT 'Umum',
  price      DECIMAL(12,2) NOT NULL,
  stock      INT           NOT NULL DEFAULT 0,
  created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

-- Data contoh: inventaris Apotek Arcana (boleh dihapus)
INSERT INTO products (name, category, price, stock) VALUES
  ('Ramuan Penyembuh',  'Ramuan',       150000, 24),
  ('Elixir Mana',       'Ramuan',       220000,  4),
  ('Gulungan Api',      'Gulungan',     480000,  9),
  ('Tongkat Kristal',   'Relik',       1800000,  2),
  ('Jamur Bulan',       'Bahan Langka',  65000,  0),
  ('Serbuk Bintang',    'Bahan Langka',  90000, 40);
