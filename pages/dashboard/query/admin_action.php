<?php 
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require '../../../backend/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user']['id'])) {
        echo json_encode(['success' => false, 'message' => 'User not authenticated.']);
        exit;
    }

    if (!isset($_POST['action'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
        exit;
    }

    $loggedInUserID = intval($_SESSION['user']['id']);
    $action = mysqli_real_escape_string($conn, $_POST['action']);
    $targetUserID = isset($_POST['targetUserID']) ;

    // Get user types
    $res = mysqli_query($conn, "SELECT type FROM nx_user_type WHERE pID = $loggedInUserID");
    $row = mysqli_fetch_assoc($res);
    $loggedInUserType = $row ? $row['type'] : null;

    $res = mysqli_query($conn, "SELECT type FROM nx_user_type WHERE pID = $targetUserID");
    $row = mysqli_fetch_assoc($res);
    $targetUserType = $row ? $row['type'] : null;

    // Log action
    $logEntry = "[" . date("Y-m-d H:i:s") . "] User $loggedInUserID performed '$action' on User $targetUserID\n";
    file_put_contents('admin_actions_log.txt', $logEntry, FILE_APPEND);

    if ($action === 'set_admin') {
        if ($targetUserType === '2') {
            echo json_encode(['success' => false, 'message' => 'User is already an admin.']);
            exit;
        }

        if ($targetUserType !== null) {
            $result = mysqli_query($conn, "UPDATE nx_user_type SET type = 2 WHERE pID = $targetUserID");
        } else {
            $result = mysqli_query($conn, "INSERT INTO nx_user_type (pID, type) VALUES ($targetUserID, 2)");
        }

    } elseif ($action === 'remove_admin') {
        if ($targetUserType !== '2') {
            echo json_encode(['success' => false, 'message' => 'Target user is not an admin.']);
            exit;
        }

        if ($targetUserType === '3' && $loggedInUserType !== '3') {
            echo json_encode(['success' => false, 'message' => 'You do not have permission to remove a super admin.']);
            exit;
        }

        $result = mysqli_query($conn, "UPDATE nx_user_type SET type = 1 WHERE pID = $targetUserID");
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
        exit;
    }

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Action completed successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to execute action: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
