<?php
session_start();
require_once '../../backend/db_connect.php'; // Include the database connection

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php'); // Redirect to login page if not logged in
    exit;
}

$user = $_SESSION['user'];
$isAdmin = ($user['user_type'] == '2'); // User type 2 is admin
$isSuperAdmin = ($user['user_type'] == '3'); // User type 3 is super admin

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
        b.batch_name,
        ut.type AS user_type
    FROM 
        nx_users u
    LEFT JOIN 
        nx_user_batches ub ON u.pID = ub.pID
    LEFT JOIN 
        nx_batches b ON ub.batchID = b.batchID
    LEFT JOIN 
        nx_user_type ut ON u.pID = ut.pID
    WHERE 
        u.fname LIKE '%$searchTerm%' OR 
        u.lname LIKE '%$searchTerm%' OR 
        CONCAT(u.fname, ' ', u.lname) LIKE '%$searchTerm%'
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

// Fetch total number of registered alumni
// Fetch total number of registered alumni (all users)
$total_alumni_query = "SELECT COUNT(*) AS total FROM nx_users"; 
$total_alumni_result = $conn->query($total_alumni_query);
$total_alumni_row = $total_alumni_result->fetch_assoc();
$total_alumni = $total_alumni_row['total'];


// Fetch registration counts by type
$registration_counts_query = "
    SELECT type, COUNT(*) AS count
    FROM nx_user_type
    GROUP BY type
";
$registration_counts_result = $conn->query($registration_counts_query);

$registration_data = [];
while ($row = $registration_counts_result->fetch_assoc()) {
    $registration_data[$row['type']] = (int)$row['count'];
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once '../header_cdn.php'; ?>
    <title>User Batch List</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Include Chart.js -->
</head>
<body class="bg-gray-100 overflow-hidden">
    <?php include '../header.php';  // Include the header ?>
    <div class="flex h-screen">
        <?php include '../sidebar.php';  // Include the sidebar ?>

        <!-- Main Content Area -->
        <div class="flex-1 p-4 md:p-6 overflow-y-auto">
            <h2 class="text-2xl font-bold mb-6">ALUMNI</h2>

            <!-- Admin Section: Display Total Alumni -->
            <?php if ($isAdmin || $isSuperAdmin): ?>
                <div class="bg-white p-4 rounded-lg shadow mb-6">
                    <h3 class="font-semibold">Total Alumni: <?php echo htmlspecialchars($total_alumni); ?></h3>
                </div>

                <!-- Bar Chart for Registration Counts -->
                 <div  class="bg-white p-4 rounded-lg shadow w-full h-[400px]">
                 <div class="h-[300px] w-full">
                    <h3 class="font-semibold mb-2">User Registration Counts by Type</h3>
                    <canvas id="userTypeChart" class="w-full"></canvas>
                </div>  
                 </div>

            <?php endif; ?>

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

                            <!-- Show buttons based on user type -->
                            <?php if ($isAdmin && $row['user_type'] != '2' && $row['user_type'] != '3'): ?>
                                <button class="relative group bg-green-500 text-white rounded-full p-2 mt-2 hover:bg-green-600 transition"
                                        onclick="setAdmin(<?php echo htmlspecialchars($row['pID']); ?>)">
                                    <i class="fas fa-user-shield"></i> <!-- Set Admin Icon -->
                                    <span class="absolute left-1/2 -translate-x-1/2 bottom-full mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        Set Admin
                                    </span>
                                </button>
                            <?php elseif ($isSuperAdmin): ?>
                                <button class="relative group bg-red-500 text-white rounded-full p-2 mt-2 hover:bg-red-600 transition"
                                        onclick="removeAdmin(<?php echo htmlspecialchars($row['pID']); ?>)">
                                    <i class="fas fa-user-slash"></i> <!-- Remove Admin Icon -->
                                    <span class="absolute left-1/2 -translate-x-1/2 bottom-full mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        Remove Admin
                                    </span>
                                </button>
                            <?php endif; ?>
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


<script>
    // Function to handle setting an admin
    function setAdmin(userID) {
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to make this user an admin?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, make admin!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Create FormData and send the request via fetch
                const formData = new FormData();
                formData.append('userID', userID); 
                formData.append('action', 'set_admin'); // define action

                fetch('../dashboard/query/admin_action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            'Success!',
                            'User has been granted admin privileges.',
                            'success'
                        ).then(() => {
                            location.reload(); // Reload the page after success
                        });
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'An error occurred while processing your request.', 'error');
                });
            }
        });
    }

    // Function to handle removing an admin
    function removeAdmin(userID) {
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to remove admin privileges from this user?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, remove admin!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Create FormData and send the request via fetch
                const formData = new FormData();
                formData.append('userID', userID);
                formData.append('action', 'remove_admin'); // define action

                fetch('../dashboard/query/admin_action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            'Success!',
                            'Admin privileges have been removed from the user.',
                            'success'
                        ).then(() => {
                            location.reload(); // Reload the page after success
                        });
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'An error occurred while processing your request.', 'error');
                });
            }
        });
    }

    // Chart.js setup
    const ctx = document.getElementById('userTypeChart').getContext('2d');
    const userTypeChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Regular', 'Admin', 'Super Admin'],
            datasets: [{
                label: 'User Registration Counts',
                data: [
                    <?php echo isset($registration_data[1]) ? $registration_data[1] : 0; ?>,
                    <?php echo isset($registration_data[2]) ? $registration_data[2] : 0; ?>,
                    <?php echo isset($registration_data[3]) ? $registration_data[3] : 0; ?>
                ],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.2)', // Green
                    'rgba(54, 162, 235, 0.2)', // Blue
                    'rgba(255, 206, 86, 0.2)'  // Gold
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)', // Green
                    'rgba(54, 162, 235, 1)', // Blue
                    'rgba(255, 206, 86, 1)'  // Gold
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
</script>
</body>
</html>
