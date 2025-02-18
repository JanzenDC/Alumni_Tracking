<?php
require 'db_connect.php';
session_start(); // Start session to use session variables

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Basic validation
    if (empty($username) || empty($password)) {
        $_SESSION['toastr_message'] = 'Please fill in all fields.';
        $_SESSION['toastr_type'] = 'error';
        header('Location: ../index.php'); // Redirect to the login page
        exit;
    }

    // Escape user inputs to prevent SQL injection
    $username = $conn->real_escape_string($username);

    // Construct the SQL query to get user data and user type
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

        // Verify the password
        if (password_verify($password, $hashed_password)) {
            // Password is correct; set session variable with all user data
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
                'user_type' => $row['user_type'], // Add user type to session
                'position' => $row['position'],     // Add employee position to session
                'department' => $row['department'], // Add employee department to session
                'hire_date' => $row['hire_date'],   // Add hire date to session
                'status' => $row['status']           // Add employment status to session
            ];
            $_SESSION['toastr_message'] = 'Login successful!';
            $_SESSION['toastr_type'] = 'success';
            header('Location: ../pages/dashboard/dashboard.php'); // Redirect to the home page
        } else {
            $_SESSION['toastr_message'] = 'Invalid username or password';
            $_SESSION['toastr_type'] = 'error';
            header('Location: ../index.php'); // Redirect to the login page
        }
    } else {
        $_SESSION['toastr_message'] = 'Invalid username or password.';
        $_SESSION['toastr_type'] = 'error';
        header('Location: ../index.php'); // Redirect to the login page
    }

    // Close   
    $conn->close();
}
?>
