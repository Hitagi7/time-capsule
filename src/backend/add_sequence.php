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

// Validate start date
$year = intval($input['startDate']['year']);
$month = intval($input['startDate']['month']);
$day = intval($input['startDate']['day']);

// Check if date is valid
if (!checkdate($month, $day, $year)) {
    echo json_encode(['success' => false, 'message' => 'Invalid date']);
    exit;
}

// Check if date is in the future
$startDate = new DateTime("$year-$month-$day");
$today = new DateTime();
$today->setTime(0, 0);

if ($startDate <= $today) {
    echo json_encode(['success' => false, 'message' => 'Please select a future date']);
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'timelocks');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Create sequences table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS sequences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

// Insert a new sequence and get its ID
$conn->query("INSERT INTO sequences () VALUES ()");
$threadId = $conn->insert_id;

$userEmail = $input['userEmail'];
$messages = $input['messages'];
$interval = intval($input['recurringInterval']);
$total = count($messages);

// Create DateTime object for the start date
$date = new DateTime();
$date->setDate($year, $month, $day);

// Validate all dates in the sequence
for ($i = 1; $i < $total; $i++) {
    $futureDate = clone $date;
    $futureDate->modify('+' . ($i * $interval) . ' years');
    
    // Check if the same day exists in the future date
    if (!checkdate($futureDate->format('n'), $day, $futureDate->format('Y'))) {
        echo json_encode(['success' => false, 'message' => 'Invalid date in sequence. The selected day might not exist in a future month.']);
        $conn->close();
        exit;
    }
}

// Reset date for actual insertion
$date = new DateTime();
$date->setDate($year, $month, $day);

for ($i = 0; $i < $total; $i++) {
    $msg = $messages[$i];
    
    if ($i > 0) {
        // Add years for subsequent messages
        $date->modify('+' . $interval . ' years');
    }
    
    $open_date = $date->format('Y-m-d');
    $sequence_number = $i + 1;

    $stmt = $conn->prepare("INSERT INTO messages (user_email, content, open_date, thread_id, sequence_number, total_in_sequence) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssii", $userEmail, $msg, $open_date, $threadId, $sequence_number, $total);
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Failed to add message: ' . $stmt->error]);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();
}

$conn->close();
echo json_encode(['success' => true, 'message' => 'Sequence added']);
exit;