<?php
session_start();
require '../../../backend/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

// Get the requesting user ID
$userID1 = $_SESSION['user']['id']; // Assuming the logged-in user's ID is stored in the session

// Get the friend's ID from the request
if (isset($_POST['friendID'])) {
    $userID2 = intval($_POST['friendID']); // Sanitize the input

    // Construct the SQL query
    $sql = "INSERT INTO nx_friends (userID1, userID2) VALUES ($userID1, $userID2)
            ON DUPLICATE KEY UPDATE status = 0"; // Update status to pending if the friendship already exists

    // Execute the SQL query
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Friend request sent!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send friend request.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}

$conn->close();
?>
