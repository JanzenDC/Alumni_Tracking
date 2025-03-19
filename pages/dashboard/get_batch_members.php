<?php
require_once '../../backend/db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON response header
header('Content-Type: application/json');

// Check if batchId is provided
if (!isset($_GET['batchId']) || empty($_GET['batchId'])) {
    echo json_encode(['success' => false, 'message' => 'Batch ID is required']);
    exit;
}

$batchId = intval($_GET['batchId']);

// Query to fetch members of the batch
$query = "SELECT u.pID, 
                 CONCAT(u.fname, ' ', COALESCE(u.mname, ''), ' ', u.lname) AS name, 
                 u.email 
          FROM nx_users u
          JOIN nx_user_batches ub ON u.pID = ub.pID
          WHERE ub.batchID = ? AND ub.is_active = 1";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $batchId);
$stmt->execute();
$result = $stmt->get_result();

$members = [];
while ($row = $result->fetch_assoc()) {
    $members[] = $row;
}

$stmt->close();
$conn->close();

// Ensure correct JSON response
echo json_encode(['success' => true, 'members' => $members]);
?>
