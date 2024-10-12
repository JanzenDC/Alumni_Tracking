<?php
session_start();
require_once '../../backend/db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

// Access user data from the session
$user = $_SESSION['user'];
$userId = $user['id'];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $batch_name = $_POST['batch_name'] ?? '';
    $batch_date = $_POST['batch_date'] ?? '';
    $profile = $_POST['profile'] ?? '';
    $description = $_POST['description'] ?? '';

    // Handle file upload
    if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['cover_photo']['tmp_name'];
        $fileName = $_FILES['cover_photo']['name'];
        $fileSize = $_FILES['cover_photo']['size'];
        $fileType = $_FILES['cover_photo']['type'];

        // Specify the upload directory
        $uploadFileDir = '../../images/batch_group_images/';
        $dest_path = $uploadFileDir . basename($fileName);

        // Check if the file is an image (optional)
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($fileType, $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type.']);
            exit;
        }

        // Move the file to the upload directory
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // Insert into database
            $sql = "INSERT INTO nx_batches (batch_name, batch_date, profile, cover_photo, description, created_at, updated_at)
                    VALUES ('$batch_name', '$batch_date', '$profile', '$fileName', '$description', NOW(), NOW())";

            if ($conn->query($sql) === TRUE) {
                echo json_encode(['success' => true, 'message' => 'Batch created successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'File upload failed.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or error occurred.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>
