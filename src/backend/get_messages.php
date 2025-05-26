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

if (!isset($input['userEmail'])) {
    echo json_encode(['success' => false, 'message' => 'Missing userEmail']);
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'timelocks');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("SELECT id, content, open_date, thread_id, sequence_number, total_in_sequence FROM messages WHERE user_email = ? ORDER BY open_date ASC");
$stmt->bind_param("s", $input['userEmail']);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $date = date_parse($row['open_date']);
    $messages[] = [
        'id' => $row['id'],
        'content' => $row['content'],
        'openDate' => [
            'year' => $date['year'],
            'month' => $date['month'],
            'day' => $date['day'],
        ],
        'threadId' => $row['thread_id'],
        'sequenceNumber' => $row['sequence_number'],
        'totalInSequence' => $row['total_in_sequence'],
        'isOpened' => false
    ];
}

echo json_encode(['success' => true, 'messages' => $messages]);
$stmt->close();
$conn->close();
exit;