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

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$user = $_SESSION['user'];
$userId = $user['id'];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Validate inputs
$batch_name = $_POST['batch_name'] ?? '';
$batch_date = $_POST['batch_date'] ?? '';
$profile = $_POST['profile'] ?? '';
$description = $_POST['description'] ?? '';

// Check for empty fields
if (empty($batch_name) || empty($batch_date) || empty($profile)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

// Handle file upload
$fileName = '';
if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['cover_photo']['tmp_name'];
    $fileName = basename($_FILES['cover_photo']['name']);
    $fileSize = $_FILES['cover_photo']['size'];
    $fileType = $_FILES['cover_photo']['type'];

    // Specify the upload directory
    $uploadFileDir = '../../../images/batch_group_images/';
    $dest_path = $uploadFileDir . $fileName;

    // Check if the file is an image
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.']);
        exit;
    }

    // Move the file to the upload directory
    if (!move_uploaded_file($fileTmpPath, $dest_path)) {
        echo json_encode(['success' => false, 'message' => 'File upload failed.']);
        exit;
    }
}

// Insert into database using prepared statements
$sql = "INSERT INTO nx_batches (batch_name, batch_date, profile, cover_photo, description, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}

// Bind parameters to prevent SQL injection
$stmt->bind_param("sssss", $batch_name, $batch_date, $profile, $fileName, $description);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Batch created successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
