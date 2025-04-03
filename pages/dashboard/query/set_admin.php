<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
require '../../../backend/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated.']);
    exit;
}

if (!isset($_POST['targetUserID'])) {
    echo json_encode(['success' => false, 'message' => 'Target user ID is missing.']);
    exit;
}

$loggedInUserID = intval($_SESSION['user']['id']);
$targetUserID = intval($_POST['targetUserID']);

// Check current user type
$res = mysqli_query($conn, "SELECT type FROM nx_user_type WHERE pID = $loggedInUserID");
$row = mysqli_fetch_assoc($res);
$loggedInUserType = $row['type'] ?? null;

// Check target user type
$res = mysqli_query($conn, "SELECT type FROM nx_user_type WHERE pID = $targetUserID");
$row = mysqli_fetch_assoc($res);
$targetUserType = $row['type'] ?? null;

// Log action
$logEntry = "[" . date("Y-m-d H:i:s") . "] User $loggedInUserID attempted to set admin for User $targetUserID\n";
file_put_contents('admin_actions_log.txt', $logEntry, FILE_APPEND);

if ($targetUserType === '2') {
    echo json_encode(['success' => false, 'message' => 'User is already an admin.']);
    exit;
}

if ($targetUserType !== null) {
    $result = mysqli_query($conn, "UPDATE nx_user_type SET type = 2 WHERE pID = $targetUserID");
} else {
    $result = mysqli_query($conn, "INSERT INTO nx_user_type (pID, type) VALUES ($targetUserID, 2)");
}

if ($result) {
    echo json_encode(['success' => true, 'message' => 'User has been set as admin.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update user: ' . mysqli_error($conn)]);
}
?>
