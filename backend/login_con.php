<?php
require 'db_connect.php';
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ----------- GeoIP + Timezone Function -----------
function getUserTimezoneFromIP($ip) {
    $apiUrl = "http://ip-api.com/json/$ip?fields=status,message,timezone";
    $response = @file_get_contents($apiUrl);
    if ($response) {
        $data = json_decode($response, true);
        if ($data['status'] === 'success' && !empty($data['timezone'])) {
            return $data['timezone'];
        }
    }
    return 'Asia/Singapore'; // Fallback timezone
}

// ----------- Alter Timestamp Column (one-time only, then remove) -----------
$alter_query = "ALTER TABLE nx_logs MODIFY COLUMN `timestamp` DATETIME NOT NULL";
if ($conn->query($alter_query) !== TRUE) {
    error_log("Table alteration failed (or already done): " . $conn->error);
}

// ----------- Process Login -----------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Basic validation
    if (empty($username) || empty($password)) {
        $_SESSION['toastr_message'] = 'Please fill in all fields.';
        $_SESSION['toastr_type'] = 'error';
        header('Location: ../index.php');
        exit;
    }

    $username = $conn->real_escape_string($username);
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    // Get user's timezone via IP
    $timezone = getUserTimezoneFromIP($ip_address);
    date_default_timezone_set($timezone);
    $current_date_time = date('Y-m-d H:i:s');

    // Query user
    $sql = "SELECT u.pID, u.email, u.fname, u.mname, u.lname, u.date_of_birth, 
                   u.profile_picture, u.bio, u.phone_number, u.address, u.city, 
                   u.state, u.zip_code, u.country, u.password_hash, 
                   ut.type AS user_type
            FROM nx_users u
            LEFT JOIN nx_user_type ut ON u.pID = ut.pID
            WHERE u.username = '$username'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $hashed_password = $row['password_hash'];

        if (password_verify($password, $hashed_password)) {
            // Set session
            $_SESSION['user'] = [
                'id' => $row['pID'],
                'username' => $username,
                'email' => $row['email'],
                'fname' => $row['fname'],
                'mname' => $row['mname'],
                'lname' => $row['lname'],
                'date_of_birth' => $row['date_of_birth'],
                'profile_picture' => $row['profile_picture'],
                'bio' => $row['bio'],
                'phone_number' => $row['phone_number'],
                'address' => $row['address'],
                'city' => $row['city'],
                'state' => $row['state'],
                'zip_code' => $row['zip_code'],
                'country' => $row['country'],
                'user_type' => $row['user_type']
            ];

            // Log success
            $log_query = "INSERT INTO nx_logs (pID, username, action, target_type, target_id, ip_address, user_agent, remark, `timestamp`) 
                          VALUES (" . $row['pID'] . ", '$username', 'login success', 'user', " . $row['pID'] . ", '$ip_address', '$user_agent', 'User successfully logged in', '$current_date_time')";
            $conn->query($log_query);

            $_SESSION['toastr_message'] = 'Login successful!';
            $_SESSION['toastr_type'] = 'success';
            header('Location: ../pages/dashboard/dashboard.php');
        } else {
            // Log failure - incorrect password
            $log_query = "INSERT INTO nx_logs (username, action, target_type, ip_address, user_agent, remark, `timestamp`) 
                          VALUES ('$username', 'login_failed', 'user', '$ip_address', '$user_agent', 'Incorrect password', '$current_date_time')";
            $conn->query($log_query);

            $_SESSION['toastr_message'] = 'Invalid username or password';
            $_SESSION['toastr_type'] = 'error';
            header('Location: ../index.php');
        }
    } else {
        // Log failure - user not found
        $log_query = "INSERT INTO nx_logs (username, action, target_type, ip_address, user_agent, remark, `timestamp`) 
                      VALUES ('$username', 'login_failed', 'user', '$ip_address', '$user_agent', 'User not found', '$current_date_time')";
        $conn->query($log_query);

        $_SESSION['toastr_message'] = 'Invalid username or password.';
        $_SESSION['toastr_type'] = 'error';
        header('Location: ../index.php');
    }

    $conn->close();
}
?>
