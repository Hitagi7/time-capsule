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

if (!isset($input['userEmail'], $input['content'], $input['openDate'])) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'timelocks');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$date = $input['openDate'];
$open_date = sprintf('%04d-%02d-%02d', $date['year'], $date['month'], $date['day']);

$threadId = isset($input['threadId']) ? $input['threadId'] : null;
$sequenceNumber = isset($input['sequenceNumber']) ? $input['sequenceNumber'] : null;
$totalInSequence = isset($input['totalInSequence']) ? $input['totalInSequence'] : null;

if ($threadId && $sequenceNumber && $totalInSequence) {
    $stmt = $conn->prepare("INSERT INTO messages (user_email, content, open_date, thread_id, sequence_number, total_in_sequence) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiii", $input['userEmail'], $input['content'], $open_date, $threadId, $sequenceNumber, $totalInSequence);
} else {
    $stmt = $conn->prepare("INSERT INTO messages (user_email, content, open_date) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $input['userEmail'], $input['content'], $open_date);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Message added']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add message']);
}

$stmt->close();
$conn->close();
exit;