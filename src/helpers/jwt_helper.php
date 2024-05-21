<?php
// src/helpers/jwt_helper.php

use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

$key = "e5b9d1e033cbe76dd937aeb4f786ae0f5e9c5d7984c7a9f0db20b928a324c805"; // Güçlü secret key

function generate_jwt($user_id, $role) {
    global $key;  // Secret key'i global olarak kullanıyoruz
    $payload = array(
        "iss" => "http://localhost",
        "aud" => "http://localhost",
        "iat" => time(),
        "nbf" => time(),
        "exp" => time() + (60*60),  // Token 1 saat geçerli olacak
        "data" => array(
            "user_id" => $user_id,
            "role" => $role
        )
    );

    $jwt = JWT::encode($payload, $key, 'HS256');
    error_log("Generated JWT: $jwt");  // JWT'yi loglayalım
    return $jwt;
}

function validate_jwt($jwt) {
    global $key;  // Secret key'i global olarak kullanıyoruz
    if (!$jwt) {
        error_log("No JWT provided.");
        return null;
    }

    try {
        error_log("Validating JWT: $jwt");  // JWT'yi loglayalım
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        error_log("Decoded JWT: " . json_encode($decoded));  // Decoded JWT'yi loglayalım
        return (array) $decoded->data;
    } catch (Exception $e) {
        error_log("JWT validation failed: " . $e->getMessage());
        return null;
    }
}
?>
