<?php
$host = 'localhost'; 
$username = 'u607308985_alumnirtu'; 
$password = '5|j[jqL8]gKV';
$database = 'u607308985_alumnirtu'; 


$conn = new mysqli($host, $username, $password, $database);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->close();
?>