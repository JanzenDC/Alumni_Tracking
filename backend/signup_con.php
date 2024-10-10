<?php
require 'db_connect.php';
session_start(); // Start session to use session variables

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = $_POST['fname'] ?? '';
    $mname = $_POST['mname'] ?? '';
    $lname = $_POST['lname'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $zip_code = $_POST['zip_code'] ?? '';
    $country = $_POST['country'] ?? '';
    $bio = $_POST['bio'] ?? '';
    // Basic validation
    if (empty($fname) || empty($lname) || empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
        $_SESSION['toastr_message'] = 'Please fill in all required fields.';
        $_SESSION['toastr_type'] = 'error';
        header('Location: ../signup.php');
        exit;
    }

    if ($password !== $confirm_password) {
        $_SESSION['toastr_message'] = 'Passwords do not match.';
        $_SESSION['toastr_type'] = 'error';
        header('Location: ../signup.php');
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Construct the SQL query
    $sql = "INSERT INTO nx_users (username, email, password_hash, fname, mname, lname, date_of_birth, bio, phone_number, address, city, state, zip_code, country) 
            VALUES ('$username', '$email', '$hashed_password', '$fname', '$mname', '$lname', '$date_of_birth', '$bio', '$phone_number', '$address', '$city', '$state', '$zip_code', '$country')";

    // Execute the query
    if ($conn->query($sql) === TRUE) {
        $_SESSION['toastr_message'] = 'Registration successful!';
        $_SESSION['toastr_type'] = 'success';
        header('Location: ../index.php');
        exit();
    } else {
        if ($conn->errno === 1062) {
            $_SESSION['toastr_message'] = 'Username or email already exists.';
            $_SESSION['toastr_type'] = 'error';
        } else {
            $_SESSION['toastr_message'] = 'Error: ' . $conn->error;
            $_SESSION['toastr_type'] = 'error';
        }
        header('Location: ../signup.php');
        exit();
    }

    // Close connection
    $conn->close();
}
?>
