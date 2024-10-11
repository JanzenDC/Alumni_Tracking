<?php
session_start();
require '../../../backend/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

// Define the target directory for profile pictures
$targetDir = '../../../images/pfp/';
$imageFileType = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
$response = [];

// Check if the file was uploaded
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $check = getimagesize($_FILES['profile_picture']['tmp_name']);
    if ($check === false) {
        $response['success'] = false;
        $response['message'] = 'File is not an image.';
        echo json_encode($response);
        exit;
    }

    // Limit file size to 2MB
    if ($_FILES['profile_picture']['size'] > 2 * 1024 * 1024) {
        $response['success'] = false;
        $response['message'] = 'File is too large. Maximum size is 2MB.';
        echo json_encode($response);
        exit;
    }

    // Allow only certain file formats
    $allowedFormats = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowedFormats)) {
        $response['success'] = false;
        $response['message'] = 'Only JPG, JPEG, PNG & GIF files are allowed.';
        echo json_encode($response);
        exit;
    }

    // Create a unique file name to avoid overwriting
    $newFileName = uniqid() . '.' . $imageFileType;
    $targetFile = $targetDir . $newFileName;

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
        // Update the user's profile picture in the database
        $userId = $_SESSION['user']['id']; // Assuming user ID is stored in session
        $stmt = $conn->prepare("UPDATE nx_users SET profile_picture = ? WHERE pID = ?");
        $stmt->bind_param("si", $newFileName, $userId);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Profile picture updated successfully.';
        } else {
            $response['success'] = false;
            $response['message'] = 'Database error: Could not update profile picture.';
        }

        $stmt->close();
    } else {
        $response['success'] = false;
        $response['message'] = 'Error moving the uploaded file.';
    }
} else {
    $response['success'] = false;
    $response['message'] = 'No file was uploaded or there was an upload error.';
}

// Close the database connection


// Return the JSON response
echo json_encode($response);
$conn->close();
?>
