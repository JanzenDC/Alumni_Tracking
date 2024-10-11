<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php'); // Redirect to login page if not logged in
    exit;
}

// Access user data from the session
$user = $_SESSION['user'];

// Database connection
require_once '../../backend/db_connect.php';

// Get batch ID from the query string
$batchID = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch batch details
$batchSql = "SELECT batch_name, batch_date, cover_photo, description FROM nx_batches WHERE batchID = ?";
$stmt = $conn->prepare($batchSql);
$stmt->bind_param('i', $batchID);
$stmt->execute();
$batchResult = $stmt->get_result();

$batchDetails = $batchResult->fetch_assoc();

// Fetch users in this batch
$usersSql = "
    SELECT u.pID, u.username, u.fname, u.lname, u.email, u.profile_picture
    FROM nx_user_batches ub
    JOIN nx_users u ON ub.pID = u.pID
    WHERE ub.batchID = ?
";
$stmtUsers = $conn->prepare($usersSql);
$stmtUsers->bind_param('i', $batchID);
$stmtUsers->execute();
$usersResult = $stmtUsers->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once '../header_cdn.php'; ?>
    <title>Batch Details</title>
</head>
<body class="bg-gray-100 overflow-hidden">
    <?php include '../header.php';  // Include the header ?>
    <div class="flex h-screen overflow-auto">
        <?php include '../sidebar.php';  // Include the sidebar ?>

        <div class="flex-1 p-6 4 md:p-6overflow-y-auto">
    <?php if ($batchDetails): ?>
        <div class="bg-white  rounded-lg shadow-lg transition-transform transform hover:scale-105">
            <h2 class="text-3xl font-bold text-gray-800 mb-2 p-3"><?php echo htmlspecialchars($batchDetails['batch_name']); ?></h2>
            <!-- <img src="../../images/batch_group_images/<?php echo htmlspecialchars($batchDetails['cover_photo']); ?>" alt="<?php echo htmlspecialchars($batchDetails['batch_name']); ?>" class="w-full h-48 object-cover rounded-lg shadow-sm mb-4"> -->
            <p class="text-gray-700 leading-relaxed mb-2 p-3"><?php echo htmlspecialchars($batchDetails['description']); ?></p>
            <p class="text-gray-500 mt-2 text-sm p-3">Batch Date: <?php echo htmlspecialchars($batchDetails['batch_date']); ?></p>
        </div>

        <div class="mt-6">
            <h3 class="text-xl font-semibold mb-4">Members of this Batch</h3>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <?php if ($usersResult->num_rows > 0): ?>
                    <ul>
                        <?php while ($user = $usersResult->fetch_assoc()): ?>
                            <li class="flex items-center mb-4 hover:bg-gray-100 p-2 rounded transition-colors">
                                <img src="../../images/pfp/<?php echo htmlspecialchars($user['profile_picture'] ?: 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($user['username']); ?>" class="w-10 h-10 rounded-full mr-3">
                                <div>
                                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($user['username']); ?></p>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($user['fname'] . ' ' . $user['lname']); ?></p>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-gray-500">No members in this batch yet.</p>
                <?php endif; ?>
            </div>
        </div>

    <?php else: ?>
        <p class="p-4 text-red-500">Batch not found.</p>
    <?php endif; ?>
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
