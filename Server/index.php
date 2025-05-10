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
elseif ($method == 'POST' && $_GET['action'] == 'login') {
    $username = $input['username'];
    $password = $input['password'];
    
    $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    $user = mysqli_fetch_assoc($result);
    
    if ($user && password_verify($password, $user['password'])) {
        echo json_encode(['success' => true, 'user' => ['id' => $user['id'], 'username' => $user['username']]]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    }
    
}
elseif ($method == 'GET' && $_GET['action'] == 'messages') {
    $result = mysqli_query($conn, "
        SELECT m.*, u.username 
        FROM messages m 
        JOIN users u ON m.user_id = u.id 
        ORDER BY m.created_at ASC 
        LIMIT 50
    ");
    $messages = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = $row;
    }
    echo json_encode($messages);
    
} elseif ($method == 'POST' && $_GET['action'] == 'messages') {
    if (empty($input['userId']) || empty($input['text']) || empty($input['username'])) {
        http_response_code(400);
        die(json_encode(['success' => false, 'message' => 'Недостаточно данных']));
    }
    
    $userId = intval($input['userId']);
    $username = mysqli_real_escape_string($conn, $input['username']);
    $text = mysqli_real_escape_string($conn, $input['text']);
    
    $sql = "INSERT INTO messages (user_id, username, text) 
            VALUES ($userId, '$username', '$text')";
    
    if (mysqli_query($conn, $sql)) {
        die(json_encode(['success' => true]));
    } else {
        error_log("MySQL error: " . mysqli_error($conn));
        http_response_code(500);
        die(json_encode(['success' => false, 'message' => 'Ошибка базы данных']));
    }
}
?>