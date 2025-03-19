<?php
session_start();
require_once '../../backend/db_connect.php';

// Ensure the user is logged in and has admin privileges
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit;
}

// Process feedback deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_id'])) {
    $feedbackId = $conn->real_escape_string($_POST['feedback_id']);
    
    $sql = "DELETE FROM nx_feedback WHERE feedback_id = '$feedbackId'";
    
    if ($conn->query($sql)) {
        // Redirect back with success message
        header('Location: manage_feedback.php?deleted=success');
    } else {
        // Redirect back with error message
        header('Location: manage_feedback.php?deleted=error');
    }
} else {
    // Redirect if accessed directly without proper parameters
    header('Location: manage_feedback.php');
}

$conn->close();
?>