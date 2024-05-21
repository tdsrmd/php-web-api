<?php
// src/database/migrations/create_files_table.php

$pdo = require __DIR__ . '/../../config/database.php';

$sql = "CREATE TABLE IF NOT EXISTS files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    upload_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)  ENGINE=INNODB;";

$pdo->exec($sql);
?>
