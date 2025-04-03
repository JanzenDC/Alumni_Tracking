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
// Get the request body for JSON input or use POST for form submissions
$requestBody = file_get_contents('php://input');
if ($requestBody) {
    // $_POST = json_decode($requestBody, true);
    $userID = intval($_POST['userID']);
    $action = $_POST['action'];
} else {
    $userID = intval($_POST['userID']);
    $action = $_POST['action'];
}

// Handle remove alumni action
if ($action === 'remove_alumni') {
    // Check if user exists and is not a super admin
    $checkQuery = "SELECT type FROM nx_user_type WHERE pID = $userID";
    $result = $conn->query($checkQuery);
    $row = $result->fetch_assoc();
    $currentType = $row['type'] ?? null;
    
    // Prevent removal of super admins
    if ($currentType == '3') {
        echo json_encode(['success' => false, 'message' => 'Cannot remove a Super Admin']);
        exit;
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Delete from nx_user_type
        $deleteUserTypeQuery = "DELETE FROM nx_user_type WHERE pID = $userID";
        $conn->query($deleteUserTypeQuery);
        
        // Delete from nx_user_batches
        $deleteUserBatchesQuery = "DELETE FROM nx_user_batches WHERE pID = $userID";
        $conn->query($deleteUserBatchesQuery);
        
        // Delete any other related records that may exist
        // Check if tables exist before attempting deletion
        $tableCheckQuery = "SHOW TABLES LIKE 'nx_user_profile'";
        $tableResult = $conn->query($tableCheckQuery);
        if ($tableResult->num_rows > 0) {
            $conn->query("DELETE FROM nx_user_profile WHERE pID = $userID");
        }
        
        $tableCheckQuery = "SHOW TABLES LIKE 'nx_user_education'";
        $tableResult = $conn->query($tableCheckQuery);
        if ($tableResult->num_rows > 0) {
            $conn->query("DELETE FROM nx_user_education WHERE pID = $userID");
        }
        
        $tableCheckQuery = "SHOW TABLES LIKE 'nx_user_employment'";
        $tableResult = $conn->query($tableCheckQuery);
        if ($tableResult->num_rows > 0) {
            $conn->query("DELETE FROM nx_user_employment WHERE pID = $userID");
        }
        
        // Finally, delete the user from nx_users
        $deleteUserQuery = "DELETE FROM nx_users WHERE pID = $userID";
        if ($conn->query($deleteUserQuery) === TRUE) {
            // Commit the transaction
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Alumni removed successfully']);
        } else {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        // Roll back the transaction in case of error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error removing alumni: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
?>