<?php
session_start();
require_once '../../backend/db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php'); // Redirect to login page if not logged in
    exit;
}

// Access user data from the session
$user = isset($_SESSION['user']) && is_array($_SESSION['user']) ? $_SESSION['user'] : [];

$userId = $_SESSION['user']['id'];
$sql = "SELECT position, department, hire_date FROM nx_employees WHERE pID = $userId";
$result = mysqli_query($conn, $sql);

$employee = [];
if ($result && mysqli_num_rows($result) > 0) {
    $employee = mysqli_fetch_assoc($result);
}

// Default values if no employee data exists
$employee['position'] = $employee['position'] ?? '';
$employee['department'] = $employee['department'] ?? '';
$employee['hire_date'] = $employee['hire_date'] ?? '';
// Update logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $fname = trim($_POST['fname']);
    $mname = trim($_POST['mname']);
    $lname = trim($_POST['lname']);
    $date_of_birth = trim($_POST['date_of_birth']);
    $job_description = trim($_POST['job_description']);
    $phone_number = trim($_POST['phone_number']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $zip_code = trim($_POST['zip_code']);
    $country = trim($_POST['country']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $position = trim($_POST['position']);
    $department = trim($_POST['department']);
    $hire_date = trim($_POST['hire_date']);

    // Validate inputs
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['toastr_message'] = 'Invalid email format.';
        $_SESSION['toastr_type'] = 'error';
    } elseif ($password !== $confirm_password) {
        $_SESSION['toastr_message'] = 'Passwords do not match.';
        $_SESSION['toastr_type'] = 'error';
    } else {
        // Escape user inputs for security
        $username = mysqli_real_escape_string($conn, $username);
        $email = mysqli_real_escape_string($conn, $email);
        $fname = mysqli_real_escape_string($conn, $fname);
        $mname = mysqli_real_escape_string($conn, $mname);
        $lname = mysqli_real_escape_string($conn, $lname);
        $date_of_birth = mysqli_real_escape_string($conn, $date_of_birth);
        $job_description = mysqli_real_escape_string($conn, $job_description);
        $phone_number = mysqli_real_escape_string($conn, $phone_number);
        $address = mysqli_real_escape_string($conn, $address);
        $city = mysqli_real_escape_string($conn, $city);
        $state = mysqli_real_escape_string($conn, $state);
        $zip_code = mysqli_real_escape_string($conn, $zip_code);
        $country = mysqli_real_escape_string($conn, $country);

        // Update user information in the database
        $sql = "UPDATE nx_users 
                SET username = '$username', 
                    email = '$email', 
                    fname = '$fname', 
                    mname = '$mname', 
                    lname = '$lname', 
                    date_of_birth = '$date_of_birth', 
                    phone_number = '$phone_number', 
                    address = '$address', 
                    city = '$city', 
                    state = '$state', 
                    zip_code = '$zip_code', 
                    country = '$country' 
                WHERE pID = $userId";

        if (mysqli_query($conn, $sql)) {
            // If password is provided, update it too
            if (!empty($password)) {
                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                $sqlPasswordUpdate = "UPDATE nx_users SET password_hash = '$password_hash' WHERE pID = $userId";
                mysqli_query($conn, $sqlPasswordUpdate);
            }

            // Update or insert employee details
            $sqlCheck = "SELECT * FROM nx_employees WHERE pID = $userId";
            $resultCheck = mysqli_query($conn, $sqlCheck);

            if (mysqli_num_rows($resultCheck) > 0) {
                // Employee record exists, perform an update
                $sqlUpdate = "UPDATE nx_employees 
                              SET position = '$position', 
                                  department = '$department', 
                                  hire_date = '$hire_date'
                              WHERE pID = $userId";
                mysqli_query($conn, $sqlUpdate);
            } else {
                // Employee record does not exist, perform an insert
                $sqlInsert = "INSERT INTO nx_employees (pID, position, department, hire_date) 
                              VALUES ($userId, '$position', '$department', '$hire_date')";
                mysqli_query($conn, $sqlInsert);
            }

            $_SESSION['toastr_message'] = 'Settings updated successfully.';
            $_SESSION['toastr_type'] = 'success';

            // Update session data
            $_SESSION['user'] = array_merge($_SESSION['user'], [
                'username' => $username,
                'email' => $email,
                'fname' => $fname,
                'mname' => $mname,
                'lname' => $lname,
                'date_of_birth' => $date_of_birth,
                'job_description' => $job_description,
                'phone_number' => $phone_number,
                'address' => $address,
                'city' => $city,
                'state' => $state,
                'zip_code' => $zip_code,
                'country' => $country
            ]);

            // Redirect to avoid form resubmission
            header('Location: settings.php');
            exit;
        } else {
            $_SESSION['toastr_message'] = 'Error updating settings: ' . mysqli_error($conn);
            $_SESSION['toastr_type'] = 'error';
        }
    }
}

// Close the database connection
mysqli_close($conn);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once '../header_cdn.php'; ?>
    <title>User Settings</title>
</head>
<body class="bg-gray-100">
    <?php include '../header.php';  // Include the header ?>
    <div class="flex h-screen ">
        <?php include '../sidebar.php';  // Include the sidebar ?>

        <!-- Main Content Area -->
        <div class="flex-1 p-4 md:p-6 overflow-y-auto">
            <h2 class="text-2xl font-bold mb-6">Settings</h2>
            <form method="POST" class="bg-white p-6 rounded-lg shadow-md">
    <!-- Step 1: User Information -->
    <div class="step step-1">
        <h2 class="text-lg font-semibold mb-4">User Information</h2>
        <div class="mb-4">
            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
            <input type="text" name="username" class="mt-1 p-2 border rounded w-full" value="<?php echo htmlspecialchars($user['username']); ?>" >
        </div>
        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" class="mt-1 p-2 border rounded w-full" value="<?php echo htmlspecialchars($user['email']); ?>" >
        </div>
        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" name="password" class="mt-1 p-2 border rounded w-full" >
        </div>
        <div class="mb-4">
            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input type="password" name="confirm_password" class="mt-1 p-2 border rounded w-full" >
        </div>
        <div class="flex justify-end">
            <button type="button" class="bg-blue-500 text-white rounded p-2 next">Next</button>
        </div>
    </div>

    <!-- Step 2: Personal Details -->
    <div class="step step-2 hidden">
        <h2 class="text-lg font-semibold mb-4">Personal Details</h2>
        <div class="mb-4">
            <label for="fname" class="block text-sm font-medium text-gray-700">First Name</label>
            <input type="text" name="fname" class="mt-1 p-2 border rounded w-full" value="<?php echo htmlspecialchars($user['fname']); ?>" >
        </div>
        <div class="mb-4">
            <label for="mname" class="block text-sm font-medium text-gray-700">Middle Name</label>
            <input type="text" name="mname" class="mt-1 p-2 border rounded w-full" value="<?php echo htmlspecialchars($user['mname']); ?>">
        </div>
        <div class="mb-4">
            <label for="lname" class="block text-sm font-medium text-gray-700">Last Name</label>
            <input type="text" name="lname" class="mt-1 p-2 border rounded w-full" value="<?php echo htmlspecialchars($user['lname']); ?>" >
        </div>
        <div class="mb-4">
            <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth</label>
            <input type="date" name="date_of_birth" class="mt-1 p-2 border rounded w-full" value="<?php echo htmlspecialchars($user['date_of_birth']); ?>">
        </div>
        <div class="flex justify-between">
            <button type="button" class="bg-gray-500 text-white rounded p-2 prev">Previous</button>
            <button type="button" class="bg-blue-500 text-white rounded p-2 next">Next</button>
        </div>
    </div>

    <!-- Step 3: Contact Information -->
    <div class="step step-3 hidden">
        <h2 class="text-lg font-semibold mb-4">Contact Information</h2>
        <div class="mb-4">
            <label for="phone_number" class="block text-sm font-medium text-gray-700">Phone Number</label>
            <input type="text" name="phone_number" class="mt-1 p-2 border rounded w-full" value="<?php echo htmlspecialchars($user['phone_number']); ?>">
        </div>
        <div class="flex justify-between">
            <button type="button" class="bg-gray-500 text-white rounded p-2 prev">Previous</button>
            <button type="button" class="bg-blue-500 text-white rounded p-2 next">Next</button>
        </div>
    </div>

    <!-- Step 4: Address Information -->
    <div class="step step-4 hidden">
        <h2 class="text-lg font-semibold mb-4">Address Information</h2>
        <div class="mb-4">
            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
            <input type="text" name="address" class="mt-1 p-2 border rounded w-full" value="<?php echo htmlspecialchars($user['address']); ?>">
        </div>
        <div class="mb-4">
            <label for="city" class="block text-sm font-medium text-gray-700">City</label>
            <input type="text" name="city" class="mt-1 p-2 border rounded w-full" value="<?php echo htmlspecialchars($user['city']); ?>">
        </div>
        <div class="mb-4">
            <label for="state" class="block text-sm font-medium text-gray-700">State</label>
            <input type="text" name="state" class="mt-1 p-2 border rounded w-full" value="<?php echo htmlspecialchars($user['state']); ?>">
        </div>
        <div class="mb-4">
            <label for="zip_code" class="block text-sm font-medium text-gray-700">Zip Code</label>
            <input type="text" name="zip_code" class="mt-1 p-2 border rounded w-full" value="<?php echo htmlspecialchars($user['zip_code']); ?>">
        </div>
        <div class="mb-4">
            <label for="country" class="block text-sm font-medium text-gray-700">Country</label>
            <input type="text" name="country" class="mt-1 p-2 border rounded w-full" value="<?php echo htmlspecialchars($user['country']); ?>">
        </div>
        <div class="flex justify-between">
            <button type="button" class="bg-gray-500 text-white rounded p-2 prev">Previous</button>
            <button type="button" class="bg-blue-500 text-white rounded p-2 next">Next</button>
        </div>
    </div>

    <!-- Step 5: Employee Details -->
    <div class="step step-5 hidden">
        <h2 class="text-lg font-semibold mb-4">Employee Details</h2>
        <div class="mb-4">
            <label for="position" class="block text-sm font-medium text-gray-700">Position</label>
            <input type="text" name="position" class="mt-1 p-2 border rounded w-full" value="<?php echo htmlspecialchars($employee['position']); ?>" >
        </div>
        <div class="mb-4">
            <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
            <input type="text" name="department" class="mt-1 p-2 border rounded w-full" value="<?php echo htmlspecialchars($employee['department']); ?>">
        </div>
        <div class="mb-4">
            <label for="hire_date" class="block text-sm font-medium text-gray-700">Hire Date</label>
            <input type="date" name="hire_date" class="mt-1 p-2 border rounded w-full" value="<?php echo htmlspecialchars($employee['hire_date']); ?>" >
        </div>
        <div class="flex justify-between">
            <button type="button" class="bg-gray-500 text-white rounded p-2 prev">Previous</button>
            <button type="submit" class="bg-blue-500 text-white rounded p-2">Submit</button>
        </div>
    </div>
</form>


        </div>
    </div>

    <!-- Toastr Notifications -->
    <?php if (isset($_SESSION['toastr_message'])): ?>
        <script>
            $(document).ready(function() {
                toastr.<?php echo $_SESSION['toastr_type']; ?>('<?php echo $_SESSION['toastr_message']; ?>');
                <?php
                unset($_SESSION['toastr_message']);
                unset($_SESSION['toastr_type']);
                ?>
            });
        </script>
    <?php endif; ?>
    <script>
        $(document).ready(function() {
            $('.next').click(function() {
                $(this).closest('.step').addClass('hidden').next('.step').removeClass('hidden');
            });

            $('.prev').click(function() {
                $(this).closest('.step').addClass('hidden').prev('.step').removeClass('hidden');
            });
        });
    </script>
</body>
</html>
