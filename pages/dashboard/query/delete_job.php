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
// Check if the user is logged in and is an admin
if (!isset($_SESSION['user']) && $_SESSION['user']['user_type'] > 2) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}


// Get job ID from POST request
$data = json_decode(file_get_contents("php://input"), true);
$jobID = $data['jobID'];

// Delete job from database
$query = "DELETE FROM nx_job_postings WHERE jobID = $jobID";
$result = $conn->query($query);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Job deleted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting job.']);
}

// Close the database connection
$conn->close();
?>
