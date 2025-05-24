<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'timelocks');

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request data']);
    exit;
}

// Validate required fields
if (!isset($data['email']) || !isset($data['password']) || !isset($data['confirmPassword'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$password = $data['password'];
$confirmPassword = $data['confirmPassword'];

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}

// Validate password match
if ($password !== $confirmPassword) {
    http_response_code(400);
    echo json_encode(['error' => 'Passwords do not match']);
    exit;
}

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Email already registered']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert new user
$stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
$stmt->bind_param("ss", $email, $hashedPassword);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(['message' => 'Account created successfully']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create account']);
}

$stmt->close();
$conn->close();