<?php
require_once '../../backend/db_connect.php';

// Set JSON response header
header('Content-Type: application/json');

// Validate department ID
if (!isset($_GET['dept_id']) || empty($_GET['dept_id'])) {
    echo json_encode(['success' => false, 'message' => 'Department ID is required']);
    exit;
}

$deptId = intval($_GET['dept_id']);

// Fetch members who joined the department
$query = "SELECT pID, CONCAT(fname, ' ', COALESCE(mname, ''), ' ', lname) AS name, email 
          FROM nx_users 
          WHERE college_department = (SELECT dept_name FROM college_departments WHERE dept_id = ?)";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$result = $stmt->get_result();

$members = [];
while ($row = $result->fetch_assoc()) {
    $members[] = $row;
}

$stmt->close();
$conn->close();

// Ensure correct JSON response
echo json_encode(['success' => true, 'members' => $members]);
?>
