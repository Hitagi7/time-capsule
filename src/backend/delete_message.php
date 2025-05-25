<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if (!isset($input['id'], $input['userEmail'])) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'timelocks');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM messages WHERE id = ? AND user_email = ?");
$stmt->bind_param("is", $input['id'], $input['userEmail']);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Message deleted']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete message']);
}

$stmt->close();
$conn->close();
exit;