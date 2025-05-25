<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if ($raw === false || $raw === '') {
    echo json_encode(['success' => false, 'message' => 'No input received']);
    exit;
}
if ($input === null) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON: ' . json_last_error_msg()]);
    exit;
}
if (!isset($input['email']) || !isset($input['password'])) {
    echo json_encode(['success' => false, 'message' => 'Missing email or password']);
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'timelocks');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("SELECT password, email FROM users WHERE email = ?");
$stmt->bind_param("s", $input['email']);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Email not found']);
    $stmt->close();
    $conn->close();
    exit;
}

$stmt->bind_result($hashed_password, $email);
$stmt->fetch();

if (password_verify($input['password'], $hashed_password)) {
    // Extract username from email (before @)
    $username = explode('@', $email)[0];
    echo json_encode([
        'success' => true,
        'message' => 'Login successful!',
        'username' => $username
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Incorrect password']);
}

$stmt->close();
$conn->close();
exit;