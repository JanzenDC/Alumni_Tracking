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
require_once '../../backend/db_connect.php'; // Include the database connection

// Initialize search variable
$search = '';
if (isset($_POST['search'])) {
    $search = trim($_POST['search']);
}

// Fetch users with their batches, including profile pictures
$query = "
    SELECT 
        u.pID,
        CONCAT(u.fname, ' ', u.lname) AS full_name,
        u.profile_picture,
        b.batch_name
    FROM 
        nx_users u
    JOIN 
        nx_user_batches ub ON u.pID = ub.pID
    JOIN 
        nx_batches b ON ub.batchID = b.batchID
    WHERE 
        u.remark = 1 AND (u.fname LIKE ? OR u.lname LIKE ? OR CONCAT(u.fname, ' ', u.lname) LIKE ?)";

// Prepare the statement
$stmt = $conn->prepare($query);
$searchTerm = "%$search%";
$stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    echo "Error: " . $conn->error;
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once '../header_cdn.php'; ?>
    <title>User Batch List</title>
</head>
<body class="bg-gray-100">
    <?php include '../header.php';  // Include the header ?>
    <div class="flex h-screen ">
        <?php include '../sidebar.php';  // Include the sidebar ?>

        <!-- Main Content Area -->
        <div class="flex-1 p-4 md:p-6 overflow-y-auto">
            <h2 class="text-2xl font-bold mb-6">ALUMNI</h2>

            <!-- Search Form -->
            <form method="POST" class="mb-4">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search by name..." 
                    value="<?php echo htmlspecialchars($search); ?>" 
                    class="border rounded p-2 w-full md:w-1/2"
                />
                <button type="submit" class="bg-blue-500 text-white rounded p-2 mt-2">Search</button>
            </form>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition-shadow">
                            <div class="flex items-center">
                                <img 
                                    src="../../images/pfp/<?php echo htmlspecialchars($row['profile_picture'] ?: '../../images/pfp/default.jpg'); ?>" 
                                    alt="<?php echo htmlspecialchars($row['full_name']); ?>" 
                                    class="w-12 h-12 rounded-full mr-3"
                                />
                                <div>
                                    <p class="font-semibold"><?php echo htmlspecialchars($row['full_name']); ?></p>
                                    <p class="text-sm text-gray-600">Batch: <?php echo htmlspecialchars($row['batch_name']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-gray-500">No users found.</p>
                <?php endif; ?>
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
