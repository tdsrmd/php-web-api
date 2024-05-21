<?php
// src/controllers/UserController.php

namespace App\Controllers;

use App\Models\User;
use \Firebase\JWT\JWT;

class UserController {
    private $userModel;

    public function __construct($pdo) {
        $this->userModel = new User($pdo);
    }

    public function register($data) {
        $username = $data['username'];
        $email = $data['email'];
        $gender = $data['gender'];
        $password = $data['password'];
        $password_confirm = $data['password_confirm'];
        $role = isset($data['role']) ? $data['role'] : 'user';  // Varsayılan rol 'user'

        // Şifreler eşleşiyor mu kontrolü
        if ($password !== $password_confirm) {
            return ['message' => 'Passwords do not match'];
        }

        // Şifreyi hash'le
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Kullanıcıyı veritabanına ekle
        try {
            $this->userModel->create($username, $email, $gender, $hashed_password, $role);
            $user_id = $this->userModel->findByEmail($email)['id'];
            $token = generate_jwt($user_id, $role);
            return ['message' => 'User registered successfully', 'token' => $token];
        } catch (\Exception $e) {
            return ['message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function login($data) {
        $email = $data['email'];
        $password = $data['password'];

        $user = $this->userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            $token = generate_jwt($user['id'], $user['role']);
            return [
                'message' => 'Login successful',
                'token' => $token,
                'user' => [
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'gender' => $user['gender'],
                    'role' => $user['role']
                ]
            ];
        } else {
            return ['message' => 'Invalid email or password'];
        }
    }
}
?>
