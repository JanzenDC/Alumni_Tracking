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

// Prevent self-removal
if ($loggedInUserID === $targetUserID) {
    echo json_encode(['success' => false, 'message' => 'You cannot remove your own admin privileges.']);
    exit;
}

// Get user types
$res = mysqli_query($conn, "SELECT type FROM nx_user_type WHERE pID = $loggedInUserID");
$row = mysqli_fetch_assoc($res);
$loggedInUserType = $row['type'] ?? null;

$res = mysqli_query($conn, "SELECT type FROM nx_user_type WHERE pID = $targetUserID");
$row = mysqli_fetch_assoc($res);
$targetUserType = $row['type'] ?? null;

// Log action
$logEntry = "[" . date("Y-m-d H:i:s") . "] User $loggedInUserID attempted to remove admin from User $targetUserID\n";
file_put_contents('admin_actions_log.txt', $logEntry, FILE_APPEND);

// Logic checks
if ($targetUserType !== '2') {
    echo json_encode(['success' => false, 'message' => 'Target user is not an admin.']);
    exit;
}

if ($targetUserType === '3' && $loggedInUserType !== '3') {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to remove a super admin.']);
    exit;
}

$result = mysqli_query($conn, "UPDATE nx_user_type SET type = 1 WHERE pID = $targetUserID");

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Admin privileges removed.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update user: ' . mysqli_error($conn)]);
}
?>
