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

// Escape the search term to prevent SQL injection
$searchTerm = $conn->real_escape_string($search);

// Pagination settings
$results_per_page = 10; // Number of results to display per page
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $results_per_page;

// Fetch total number of users for pagination with search filter
$total_query = "SELECT COUNT(*) AS total FROM nx_users WHERE fname LIKE '%$searchTerm%' OR lname LIKE '%$searchTerm%' OR CONCAT(fname, ' ', lname) LIKE '%$searchTerm%'";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_users = $total_row['total'];
$total_pages = ceil($total_users / $results_per_page);

// Fetch users with pagination and search filter
$query = "
    SELECT 
        u.pID,
        CONCAT(u.fname, ' ', u.lname) AS full_name,
        u.profile_picture,
        b.batch_name
    FROM 
        nx_users u
    LEFT JOIN 
        nx_user_batches ub ON u.pID = ub.pID
    LEFT JOIN 
        nx_batches b ON ub.batchID = b.batchID
    WHERE 
        u.fname LIKE '%$searchTerm%' OR u.lname LIKE '%$searchTerm%' OR CONCAT(u.fname, ' ', u.lname) LIKE '%$searchTerm%'
    LIMIT $results_per_page OFFSET $offset";

// Execute the query
$result = $conn->query($query);

if ($result === false) {
    echo "Error: " . $conn->error;
    exit;
}

// Initialize users array
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
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
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $row): ?>
                        <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition-shadow">
                            <a href="user_profile.php?id=<?php echo htmlspecialchars($row['pID']); ?>" class="flex items-center">
                                <img 
                                    src="../../images/pfp/<?php echo htmlspecialchars($row['profile_picture'] ?: 'default.jpg'); ?>" 
                                    alt="<?php echo htmlspecialchars($row['full_name']); ?>" 
                                    class="w-12 h-12 rounded-full mr-3"
                                />
                                <div>
                                    <p class="font-semibold"><?php echo htmlspecialchars($row['full_name']); ?></p>
                                    <p class="text-sm text-gray-600">Batch: <?php echo htmlspecialchars($row['batch_name'] ?: 'No Batch'); ?></p>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500">No users found.</p>
                <?php endif; ?>
            </div>

            <!-- Pagination Links -->
            <div class="mt-6">
                <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                    <a href="?page=<?php echo $page; ?>&search=<?php echo urlencode($search); ?>" class="bg-blue-500 text-white rounded px-3 py-1 <?php echo ($page == $current_page) ? 'font-bold' : ''; ?>">
                        <?php echo $page; ?>
                    </a>
                <?php endfor; ?>
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
