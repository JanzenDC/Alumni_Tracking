<?php
session_start();
require '../../../backend/db_connect.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user']) || ($_SESSION['user']['user_type'] !== '2' && $_SESSION['user']['user_type'] !== '3')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}


// Get data from POST request
$title = $_POST['job_title'];
$description = $_POST['job_description'];
$postedBy = $_POST['posted_by']; // Now comes from the front end

// Insert job into database
$query = "INSERT INTO nx_job_postings (title, description, posted_by) VALUES ('$title', '$description', $postedBy)";
$result = $conn->query($query);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Job created successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error creating job.']);
}

// Close the database connection
$conn->close();
?>
