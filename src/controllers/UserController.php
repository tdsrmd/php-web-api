<?php


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
        $confirmPassword = $data['confirmPassword'];
        $role = isset($data['role']) ? $data['role'] : 'user';


        if ($password !== $confirmPassword) {
            return ['message' => 'Parolalar eşleşmiyor.'];
        }


        $hashed_password = password_hash($password, PASSWORD_BCRYPT);


        try {
            $this->userModel->create($username, $email, $gender, $hashed_password, $role);
            $user_id = $this->userModel->findByEmail($email)['id'];
            $token = generate_jwt($user_id, $role);
            return ['message' => 'User registered successfully', 'token' => $token];
        } catch (\Exception $e) {
            if ($e->getCode() == 23000) {
                $errorMessage = $e->getMessage();
                if (strpos($errorMessage, 'users.email') !== false) {
                    return ['error' => 'Email kullanımda.'];
                }
                return ['error' => 'Username kullanımda.'];
            }

            return ['error' => $e->getMessage()];
        }
    }

    public function login($data) {
        $username = $data['username'];
        $password = $data['password'];

        $user = $this->userModel->findByUsername($username);

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
            return ['error' => 'Kullanıcı adı veya şifre hatalı'];
        }
    }
}
?>
