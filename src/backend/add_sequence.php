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

if (!isset($input['userEmail'], $input['messages'], $input['startDate'], $input['recurringInterval'])) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'timelocks');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$userEmail = $input['userEmail'];
$messages = $input['messages'];
$startDate = $input['startDate'];
$interval = intval($input['recurringInterval']);
$threadId = time();
$total = count($messages);

for ($i = 0; $i < $total; $i++) {
    $msg = $messages[$i];
    $date = mktime(0, 0, 0, $startDate['month'], $startDate['day'], $startDate['year'] + ($i * $interval));
    $open_date = date('Y-m-d', $date);

    $stmt = $conn->prepare("INSERT INTO messages (user_email, content, open_date, thread_id, sequence_number, total_in_sequence) VALUES (?, ?, ?, ?, ?, ?)");
    $seqNum = $i + 1;
    $stmt->bind_param("sssiii", $userEmail, $msg, $open_date, $threadId, $seqNum, $total);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
echo json_encode(['success' => true, 'message' => 'Sequence added']);
exit;