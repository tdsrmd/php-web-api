<?php


namespace App\Controllers;

class FileController {
    private $pdo;
    private $uploadDirectory;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->uploadDirectory = __DIR__ . '/../../uploads/';
    }

    public function upload($files, $jwt) {
        $userData = validate_jwt($jwt);
        if (!$userData) {
            return ['message' => 'Unauthorized'];
        }

        if (!isset($files['file'])) {
            return ['message' => 'No file uploaded'];
        }

        $file = $files['file'];


        if ($file['type'] !== 'application/pdf') {
            return ['message' => 'Only PDF files are allowed'];
        }


        $userId = $userData['user_id'];
        $userDirectory = $this->uploadDirectory . $userId;
        if (!file_exists($userDirectory)) {
            mkdir($userDirectory, 0777, true);
        }


        $originalFileName = pathinfo($file['name'], PATHINFO_FILENAME);
        $shortFileName = substr($originalFileName, 0, 6);


        $newFileName = $shortFileName . '_' . date('YmdHis') . '.pdf';
        $targetPath = $userDirectory . '/' . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {

            $stmt = $this->pdo->prepare('INSERT INTO files (user_id, file_name) VALUES (?, ?)');
            $stmt->execute([$userId, $newFileName]);

            return ['message' => 'File uploaded successfully', 'file' => $newFileName];
        } else {
            return ['message' => 'Failed to upload file'];
        }
    }

    public function getUserFiles($jwt) {
        $userData = validate_jwt($jwt);
        if (!$userData) {
            return ['message' => 'Unauthorized'];
        }

        $userId = $userData['user_id'];


        $stmt = $this->pdo->prepare('SELECT * FROM files WHERE user_id = ?');
        $stmt->execute([$userId]);
        $files = $stmt->fetchAll(\PDO::FETCH_ASSOC);


        $fileUrls = [];
        foreach ($files as $file) {
            $fileUrls[] = [
                'file_name' => $file['file_name'],
                'status' => $file['status'],
                'upload_time' => $file['upload_time'],
                'url' => 'http://localhost:8000/download/' . $userId . '/' . $file['file_name'],
            ];
        }

        return ['files' => $fileUrls];
    }

    public function downloadFile($userId, $fileName) {
        $filePath = $this->uploadDirectory . $userId . '/' . $fileName;
        error_log("Attempting to download file from path: $filePath");

        if (file_exists($filePath)) {
            error_log("File found: $filePath");
            header('Content-Description: File Transfer');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            error_log("File not found at path: $filePath");
            header('HTTP/1.1 404 Not Found');
            echo json_encode(['message' => 'File not found']);
        }
    }
}
?>
