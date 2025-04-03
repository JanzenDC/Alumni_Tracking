<?php
session_start();
require_once '../../../backend/db_connect.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

// Ensure that the request method is POST and that batchId is provided
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!isset($_POST['batchId'])) {
    echo json_encode(['success' => false, 'message' => 'Batch ID not provided.']);
    exit;
}

// Sanitize the batchId
$batchId = intval($_POST['batchId']);

// Delete the batch
$sql = "DELETE FROM nx_batches WHERE batchID = $batchId";

if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true, 'message' => 'Batch deleted successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting batch: ' . $conn->error]);
}

$conn->close();
?>