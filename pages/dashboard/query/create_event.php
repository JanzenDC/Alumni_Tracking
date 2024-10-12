<?php
session_start();
require '../../../backend/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

// Get user input
$event_name = $_POST['event_name'] ?? '';
$description = $_POST['description'] ?? '';
$event_date = $_POST['event_date'] ?? '';

// Validate input
if (empty($event_name) || empty($event_date)) {
    echo json_encode(['success' => false, 'message' => 'Event name and date are required.']);
    exit;
}

// Prepare and execute the SQL statement
$stmt = $conn->prepare("INSERT INTO nx_events (event_name, description, event_date, created_by) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssi", $event_name, $description, $event_date, $_SESSION['user']['id']);

if ($stmt->execute()) {
    // Success response
    echo json_encode(['success' => true, 'message' => 'Event created successfully.']);
} else {
    // Error response
    echo json_encode(['success' => false, 'message' => 'Error creating event: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
