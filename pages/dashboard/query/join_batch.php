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
if (!isset($_SESSION['user']) || !isset($_POST['batchId'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$userId = $_SESSION['user']['id'];
$batchId = $_POST['batchId'];

$checkQuery = "SELECT * FROM nx_user_batches WHERE pID = $userId";
$result = $conn->query($checkQuery);

if ($result->num_rows > 0) {
    $deleteQuery = "DELETE FROM nx_user_batches WHERE pID = $userId";
    $conn->query($deleteQuery);
}

$insertQuery = "INSERT INTO nx_user_batches (pID, batchID, is_active) VALUES ($userId, $batchId, 1)";
if ($conn->query($insertQuery) === TRUE) {
    echo json_encode(['success' => true, 'message' => 'Batch updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating batch']);
}

$conn->close();
?>
