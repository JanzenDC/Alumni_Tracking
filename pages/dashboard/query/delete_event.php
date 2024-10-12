<?php
session_start();
require '../../../backend/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

// Get the event ID from the POST request
$eventId = $_POST['eventID'] ?? null;

if ($eventId) {
    // Sanitize the input to prevent SQL injection
    $eventId = intval($eventId); // Convert to integer

    // Create the SQL delete statement
    $sql = "DELETE FROM nx_events WHERE eventID = $eventId";

    // Execute the delete query
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Event deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting event: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Event ID not provided.']);
}

$conn->close();
?>
