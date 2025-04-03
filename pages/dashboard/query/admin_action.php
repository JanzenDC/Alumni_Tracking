<?php 
session_start();
require '../../../backend/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user']['id'])) {
        echo json_encode(['success' => false, 'message' => 'User not authenticated.']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['action'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
        exit;
    }

    $loggedInUserID = intval($_SESSION['user']['id']);
    $action = $conn->real_escape_string($data['action']);

    // Optionally allow targeting a different user (only for admin actions)
    $targetUserID = isset($data['targetUserID']) ? intval($data['targetUserID']) : $loggedInUserID;

    // Get type of logged-in user
    $result = $conn->query("SELECT type FROM nx_user_type WHERE pID = $loggedInUserID");
    $loggedInUserType = ($result && $row = $result->fetch_assoc()) ? $row['type'] : null;

    // Get type of target user
    $result = $conn->query("SELECT type FROM nx_user_type WHERE pID = $targetUserID");
    $targetUserType = ($result && $row = $result->fetch_assoc()) ? $row['type'] : null;

    if ($action === 'set_admin') {
        if ($targetUserType === '2') {
            echo json_encode(['success' => false, 'message' => 'User is already an admin.']);
            exit;
        }
        if ($targetUserType !== null) {
            $conn->query("DELETE FROM nx_user_type WHERE pID = $targetUserID");
        }
        $result = $conn->query("INSERT INTO nx_user_type (pID, type) VALUES ($targetUserID, 2)");
    } elseif ($action === 'remove_admin') {
        if ($targetUserType !== '2' && $targetUserType !== '3') {
            echo json_encode(['success' => false, 'message' => 'Target user is not an admin.']);
            exit;
        }

        if ($targetUserType === '3' && $loggedInUserType !== '3') {
            echo json_encode(['success' => false, 'message' => 'You do not have permission to remove a super admin.']);
            exit;
        }

        $result = $conn->query("DELETE FROM nx_user_type WHERE pID = $targetUserID");
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
        exit;
    }

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Action completed successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to execute action: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
