<?php
session_start();
require '../../../backend/db_connect.php';

if (!isset($_SESSION['user']) || !isset($_POST['batchId'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$userId = $_SESSION['user']['id'];
$batchId = $_POST['batchId'];

// Check if the user is already in an active batch
$checkQuery = "SELECT batchID FROM nx_user_batches WHERE pID = $userId AND is_active = 1";
$result = $conn->query($checkQuery);

if ($result && $result->num_rows > 0) {
    // If the user is already in an active batch, deactivate it first
    $deactivateQuery = "UPDATE nx_user_batches SET is_active = 0 WHERE pID = $userId";
    if ($conn->query($deactivateQuery)) {
        // Now join the new batch
        $joinQuery = "INSERT INTO nx_user_batches (pID, batchID, is_active) VALUES ($userId, $batchId, 1)";
        if ($conn->query($joinQuery)) {
            echo json_encode(['success' => true, 'message' => 'Successfully joined the batch']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error joining the batch']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deactivating the previous batch']);
    }
} else {
    // If not in any active batch, directly join the new batch
    $joinQuery = "INSERT INTO nx_user_batches (pID, batchID, is_active) VALUES ($userId, $batchId, 1)";
    if ($conn->query($joinQuery)) {
        echo json_encode(['success' => true, 'message' => 'Successfully joined the batch']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error joining the batch']);
    }
}

$conn->close();
?>
