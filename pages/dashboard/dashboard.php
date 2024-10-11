<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php'); // Redirect to login page if not logged in
    exit;
}

// Access user data from the session
$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once '../header_cdn.php'; ?>
    <title>User Dashboard</title>
</head>
<body class="bg-gray-100">
    <?php include '../header.php';  // Include the header ?>
    <div class="flex h-screen ">
        <?php include '../sidebar.php';  // Include the sidebar ?>

        <!-- Main Content Area -->
        <div class="flex-1 p-4 md:p-6 overflow-y-auto">
            <h1 class="text-2xl font-bold mb-4">Welcome, <?php echo $user['fname']; ?>!</h1>
            <p class="mt-2">Here is your dashboard content.</p>

            <!-- Additional content can go here -->
            <div class="mt-4 bg-white shadow rounded-lg p-4">
                <h2 class="text-lg font-semibold">Dashboard Overview</h2>
                <p class="mt-2">This is where you can put an overview of your application or user-specific data.</p>
            </div>

            <!-- Example of more content -->
            <div class="mt-4 bg-white shadow rounded-lg p-4">
                <h2 class="text-lg font-semibold">Recent Activities</h2>
                <ul class="list-disc pl-5">
                    <li>Activity 1</li>
                    <li>Activity 2</li>
                    <li>Activity 3</li>
                </ul>
            </div>
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
</body>
</html>
