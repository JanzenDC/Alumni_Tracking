<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require '../../../backend/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}
// Get the requesting user ID
$userID1 = $_SESSION['user']['id'];

// Get the friend's ID from the request
if (isset($_POST['friendID'])) {
    $userID2 = intval($_POST['friendID']); // Sanitize the input

    // Construct the SQL query to delete the friend request if status is 0
    $sql = "DELETE FROM nx_friends 
            WHERE userID1 = $userID1 AND userID2 = $userID2 AND status = 0";

    // Execute the SQL query
    if ($conn->query($sql) === TRUE) {
        if ($conn->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Friend request canceled.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No pending friend request found.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to cancel friend request.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}

$conn->close();
?>
