<?php
session_start();
require_once '../../backend/db_connect.php';
// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php'); // Redirect to login page if not logged in
    exit;
}

// Access user data from the session
$user = $_SESSION['user'];

// Database connection (ensure this is set up correctly)


// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; // Number of jobs per page
$offset = ($page - 1) * $limit;

// Fetch job listings
$query = "SELECT * FROM nx_job_postings LIMIT $limit OFFSET $offset";
$result = $conn->query($query);

// Count total jobs for pagination
$totalJobsQuery = "SELECT COUNT(*) as total FROM nx_job_postings";
$totalResult = $conn->query($totalJobsQuery);
$totalJobs = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalJobs / $limit);
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
    <div class="flex h-screen">
        <?php include '../sidebar.php';  // Include the sidebar ?>

        <!-- Main Content Area -->
        <div class="flex-1 p-4 md:p-6 overflow-y-auto">
            <h2 class="text-2xl font-bold mb-6">Job Listings</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($job = $result->fetch_assoc()): ?>
                        <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition-shadow">
                            <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($job['title']); ?></h3>
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($job['description']); ?></p>
                            <p class="text-gray-800 font-bold">Posted by User ID: <?php echo htmlspecialchars($job['posted_by']); ?></p>
                            <p class="text-gray-500 text-sm">Posted on: <?php echo date('Y-m-d h:i A', strtotime($job['created_at'])); ?></p>
                            <!-- <a href="job_detail.php?id=<?php echo $job['jobID']; ?>" class="text-blue-500 hover:underline mt-4 block">View Details</a> -->
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-gray-500">No job listings available.</p>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center mt-6">
                <nav>
                    <ul class="flex space-x-2">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li>
                                <a href="?page=<?php echo $i; ?>" class="px-4 py-2 <?php echo $i === $page ? 'text-white bg-blue-600' : 'text-gray-700 bg-white border'; ?> rounded hover:bg-blue-600 hover:text-white transition">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <li>
                            <a href="?page=<?php echo $page < $totalPages ? $page + 1 : $totalPages; ?>" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">Next</a>
                        </li>
                    </ul>
                </nav>
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