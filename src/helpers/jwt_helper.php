<?php


use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

$key = "e5b9d1e033cbe76dd937aeb4f786ae0f5e9c5d7984c7a9f0db20b928a324c805";

function generate_jwt($user_id, $role) {
    global $key;
    $payload = array(
        "iss" => "http://localhost",
        "aud" => "http://localhost",
        "iat" => time(),
        "nbf" => time(),
        "exp" => time() + (60*60),
        "data" => array(
            "user_id" => $user_id,
            "role" => $role
        )
    );

    $jwt = JWT::encode($payload, $key, 'HS256');
    return $jwt;
}

function validate_jwt($jwt) {
    global $key;
    if (!$jwt) {

        return null;
    }

    try {
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        return (array) $decoded->data;
    } catch (Exception $e) {

        return null;
    }
}
?>
