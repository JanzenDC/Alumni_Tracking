<?php 
require '../../../backend/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode the JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate the required data
    if (!isset($data['userID'], $data['action'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
        exit;
    }

    $userID = intval($data['userID']);
    $action = $data['action'];

    // Get the current user type using a prepared statement for security
    $stmt = $conn->prepare("SELECT type FROM nx_user_type WHERE pID = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $currentType = $row['type'] ?? null;
    $stmt->close();

    // Decide which action to perform based on the request
    if ($action === 'set_admin') {
        // If user is already an admin, do not process further
        if ($currentType === '2') {
            echo json_encode(['success' => false, 'message' => 'User is already an admin.']);
            exit;
        }
        // Use INSERT ... ON DUPLICATE KEY UPDATE to set admin type (assumes pID is unique)
        $query = "INSERT INTO nx_user_type (pID, type) VALUES (?, 2) ON DUPLICATE KEY UPDATE type = 2";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            exit;
        }
        $stmt->bind_param("i", $userID);
    } elseif ($action === 'remove_admin') {
        // Only allow removal if the user is currently an admin (type '2')
        if ($currentType !== '2') {
            echo json_encode(['success' => false, 'message' => 'User is not an admin.']);
            exit;
        }
        // Delete the record to remove admin rights
        $query = "DELETE FROM nx_user_type WHERE pID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            exit;
        }
        $stmt->bind_param("i", $userID);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
        exit;
    }

    // Execute the query and output the result
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Action completed successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to execute action: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
