<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

require 'app/config/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);



if ($method == 'POST' && $_GET['action'] == 'register') {
    $username = $input['username'];
    $password = password_hash($input['password'], PASSWORD_BCRYPT);

    $check = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if (mysqli_num_rows($check) > 0) {
        echo json_encode(['success' => false, 'message' => 'Username taken']);
        exit;
    }

    mysqli_query($conn, "INSERT INTO users (username, password) VALUES ('$username', '$password')");
    echo json_encode(['success' => true]);
}
?>