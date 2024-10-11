<?php
session_start();
require '../../../backend/db_connect.php';

if (!isset($_SESSION['user']) || !isset($_POST['batchId'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$userId = $_SESSION['user']['id'];
$batchId = $_POST['batchId'];

// Update the user's batch
$updateQuery = "
    UPDATE nx_user_batches 
    SET is_active = 1 
    WHERE pID = $userId AND batchID = $batchId";

if ($conn->query($updateQuery) === TRUE) {
    // Deactivate any other active batches
    $deactivateQuery = "UPDATE nx_user_batches SET is_active = 0 WHERE pID = $userId AND batchID != $batchId";
    $conn->query($deactivateQuery); // Deactivate other batches silently

    echo json_encode(['success' => true, 'message' => 'Successfully joined the batch']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error joining the batch']);
}

$conn->close();
?>
