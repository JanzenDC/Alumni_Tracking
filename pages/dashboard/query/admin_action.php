<?php
require '../../../backend/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID = intval($_POST['userID']);
    $action = $_POST['action'];

    $checkQuery = "SELECT type FROM nx_user_type WHERE pID = $userID";
    $result = $conn->query($checkQuery);
    $row = $result->fetch_assoc();
    $currentType = $row['type'] ?? null;

    if ($action === 'set_admin' && $currentType !== '2') {
        $query = "INSERT INTO nx_user_type (pID, type) VALUES ($userID, 2) ON DUPLICATE KEY UPDATE type = 2";
    } elseif ($action === 'remove_admin' && $currentType !== '3') {
        $query = "DELETE FROM nx_user_type WHERE pID = $userID";
    } else {
        echo json_encode(['success' => false, 'message' => 'Action not allowed.']);
        exit;
    }

    if ($conn->query($query) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Action completed successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to execute action: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
