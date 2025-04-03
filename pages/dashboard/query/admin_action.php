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

    $userID = intval($_SESSION['user']['id']);
    $action = $conn->real_escape_string($data['action']);

    $query = "SELECT type FROM nx_user_type WHERE pID = $userID";
    $result = $conn->query($query);
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    $row = $result->fetch_assoc();
    $currentType = $row['type'] ?? null;

    if ($action === 'set_admin') {
        if ($currentType === '2') {
            echo json_encode(['success' => false, 'message' => 'User is already an admin.']);
            exit;
        }
        if ($currentType !== null) {
            $deleteQuery = "DELETE FROM nx_user_type WHERE pID = $userID";
            if (!$conn->query($deleteQuery)) {
                echo json_encode(['success' => false, 'message' => 'Failed to delete existing record: ' . $conn->error]);
                exit;
            }
        }
        $insertQuery = "INSERT INTO nx_user_type (pID, type) VALUES ($userID, 2)";
        $result = $conn->query($insertQuery);
    } elseif ($action === 'remove_admin') {
        if ($currentType !== '2') {
            echo json_encode(['success' => false, 'message' => 'User is not an admin.']);
            exit;
        }
        $deleteQuery = "DELETE FROM nx_user_type WHERE pID = $userID";
        $result = $conn->query($deleteQuery);
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
