<?php
require 'db.php';

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
        die("Please fill in all required fields.");
    }

    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Handle profile picture upload if provided
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $uploads_dir = 'uploads/';
        $tmp_name = $_FILES['profile_picture']['tmp_name'];
        $name = basename($_FILES['profile_picture']['name']);
        $profile_picture = $uploads_dir . uniqid() . "_" . $name;
        move_uploaded_file($tmp_name, $profile_picture);
    }

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO nx_users (username, email, password_hash, fname, mname, lname, date_of_birth, profile_picture, bio, phone_number, address, city, state, zip_code, country) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssssssss", $username, $email, $hashed_password, $fname, $mname, $lname, $date_of_birth, $profile_picture, $bio, $phone_number, $address, $city, $state, $zip_code, $country);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Registration successful!";
    } else {
        if ($conn->errno === 1062) { // Duplicate entry error code
            die("Username or email already exists.");
        } else {
            die("Error: " . $stmt->error);
        }
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>
<script src="node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<script src="node_modules/sweetalert2/dist/sweetalert2.min.css"></script>
<script>
    document.querySelector('form').onsubmit = function() {
        let inputs = this.querySelectorAll('input[required]');
        for (let input of inputs) {
            if (input.value === '') {
                swal("Oops!", "Please fill in all required fields.", "error");
                return false; // Prevent form submission
            }
        }
    };
</script>

