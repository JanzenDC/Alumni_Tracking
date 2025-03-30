<?php
$host = 'localhost';
$dbname = 'u607308985_alumnirtu';
$user = 'u607308985_alumnirtu';
$pass = '5|j[jqL8]gKV';

// $host = 'localhost'; 
// $user = 'root'; 
// $pass = '';
// $dbname = 'alumni_rtu';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
