<?php
require '../../../backend/db_connect.php';

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID = $_POST['userID'];
    $action = $_POST['action'];

    // Sanitize user input
    $userID = intval($userID); // Convert to integer to mitigate injection risk
    $query = "";

    if ($action === 'set_admin') {
        // Insert user as admin (type = 2)
        $query = "INSERT INTO nx_user_type (pID, type) VALUES ($userID, 2)
                  ON DUPLICATE KEY UPDATE type = 2";
    } elseif ($action === 'remove_admin') {
        // Delete the user type entry
        $query = "DELETE FROM nx_user_type WHERE pID = $userID";
    }

    // Execute the query
    if ($conn->query($query) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Action completed successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to execute action: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
