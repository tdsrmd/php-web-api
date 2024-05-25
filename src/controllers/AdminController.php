<?php

namespace App\Controllers;

class AdminController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function listAllFiles() {

        $stmt = $this->pdo->prepare('
            SELECT
                files.id AS file_id,
                files.file_name,
                files.upload_time,
                files.status,
                users.id AS user_id,
                users.username,
                users.email,
                users.gender,
                users.role
            FROM files
            JOIN users ON files.user_id = users.id
            ORDER BY files.upload_time DESC
        ');
        $stmt->execute();
        $files = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return ['files' => $files];
    }

    public function updateFileStatus($fileId, $status) {

        $stmt = $this->pdo->prepare('UPDATE files SET status = ? WHERE id = ?');
        $stmt->execute([$status, $fileId]);

        if ($stmt->rowCount() > 0) {
            return ['message' => 'Dosya statüsü başarıyla güncellendi'];
        } else {
            return ['message' => 'Dosya statüsü güncellenirken bir hata oluştu'];
        }
    }

    public function listAllUsers(){

        $stmt = $this->pdo->prepare('SELECT * FROM users');
        $stmt->execute();
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return ['users' => $users];
    }
}
?>
