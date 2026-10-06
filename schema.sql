CREATE DATABASE IF NOT EXISTS todo_api
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE todo_api;

DROP TABLE IF EXISTS todos;

CREATE TABLE todos (
    id     INT AUTO_INCREMENT PRIMARY KEY,
    item   VARCHAR(255) NOT NULL,
    status ENUM('NOT DONE', 'DONE') NOT NULL DEFAULT 'NOT DONE'
) ENGINE = InnoDB;

INSERT INTO todos (item, status) VALUES
    ('Boodschappen doen',        'NOT DONE'),
    ('Huiswerk PHP afmaken',     'NOT DONE'),
    ('POP inleveren bij SLBer',  'DONE');
