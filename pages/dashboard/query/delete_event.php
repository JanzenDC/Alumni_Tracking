<?php
session_start();
require '../../../backend/db_connect.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
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
        $_SESSION['message'] = 'Event deleted successfully.';
        header('Location: ../dashboard.php');
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting event: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Event ID not provided.']);
}

$conn->close();
?>
