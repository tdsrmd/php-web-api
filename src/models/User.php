<?php
// src/models/User.php

namespace App\Models;

class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($username, $email, $gender, $password, $role = 'user') {
        $stmt = $this->pdo->prepare('INSERT INTO users (username, email, gender, password, role) VALUES (?, ?, ?, ?, ?)');
        return $stmt->execute([$username, $email, $gender, $password, $role]);
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    public function findByUsername($username) {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
}
?>
