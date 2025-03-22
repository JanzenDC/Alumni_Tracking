<?php
session_start();
require '../../../backend/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

// Alter table to add image column if it doesn't exist
$alterQuery = "ALTER TABLE nx_events ADD COLUMN IF NOT EXISTS image VARCHAR(255)";
$conn->query($alterQuery);

// Get user input
$event_name = $_POST['event_name'] ?? '';
$description = $_POST['description'] ?? '';
$event_date = $_POST['event_date'] ?? '';
$created_by = $_SESSION['user']['id'];

// Validate input
if (empty($event_name) || empty($event_date)) {
    echo json_encode(['success' => false, 'message' => 'Event name and date are required.']);
    exit;
}

// Handle image upload
$imageFileName = null;

if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileType = mime_content_type($_FILES['event_image']['tmp_name']);

    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, or GIF images are allowed.']);
        exit;
    }

    $uploadDir = '../../../images/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $imageFileName = uniqid('event_') . '_' . basename($_FILES['event_image']['name']);
    $uploadPath = $uploadDir . $imageFileName;

    if (!move_uploaded_file($_FILES['event_image']['tmp_name'], $uploadPath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload image.']);
        exit;
    }
}

// Prepare and execute SQL insert
$stmt = $conn->prepare("INSERT INTO nx_events (event_name, description, event_date, created_by, image) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssis", $event_name, $description, $event_date, $created_by, $imageFileName);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Event created successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error creating event: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
