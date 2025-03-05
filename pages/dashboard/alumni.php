<?php
session_start();
require_once '../../backend/db_connect.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit;
}

$user = $_SESSION['user'];
$isAdmin = ($user['user_type'] == '2');
$isSuperAdmin = ($user['user_type'] == '3');

$search = '';
if (isset($_POST['search'])) {
    $search = trim($_POST['search']);
}

$searchTerm = $conn->real_escape_string($search);

$results_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $results_per_page;

$total_query = "SELECT COUNT(*) AS total FROM nx_users WHERE fname LIKE '%$searchTerm%' OR lname LIKE '%$searchTerm%' OR CONCAT(fname, ' ', lname) LIKE '%$searchTerm%'";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_users = $total_row['total'];
$total_pages = ceil($total_users / $results_per_page);

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

$result = $conn->query($query);

if ($result === false) {
    echo "Error: " . $conn->error;
    exit;
}

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$total_alumni_query = "SELECT COUNT(*) AS total FROM nx_users";
$total_alumni_result = $conn->query($total_alumni_query);
$total_alumni_row = $total_alumni_result->fetch_assoc();
$total_alumni = $total_alumni_row['total'];

$registration_counts_query = "
    SELECT 
        (SELECT COUNT(*) FROM nx_users) AS total_users,
        (SELECT COUNT(*) FROM nx_user_type WHERE type IN (2, 3)) AS admin_count,
        (SELECT COUNT(*) FROM nx_user_type WHERE type IN (2, 3)) AS alumni_count
";

$registration_counts_result = $conn->query($registration_counts_query);

if ($registration_counts_result->num_rows > 0) {
    $row = $registration_counts_result->fetch_assoc();
    
    $registration_data = [
        'total_users' => (int)$row['total_users'],
        'admin_count' => (int)$row['admin_count'],
        'alumni_count' => (int)$row['alumni_count']
    ];
    
    $total_registration = $row['total_users']; // Total registered users
} 


// Get Batch Distribution
$batch_distribution_query = "
    SELECT b.batch_name, COUNT(ub.pID) AS user_count
    FROM nx_batches b
    LEFT JOIN nx_user_batches ub ON b.batchID = ub.batchID
    GROUP BY b.batch_name
    ORDER BY user_count DESC
    LIMIT 10
";
$batch_distribution_result = $conn->query($batch_distribution_query);

$batch_data = [];
while ($row = $batch_distribution_result->fetch_assoc()) {
    $batch_data[$row['batch_name']] = (int)$row['user_count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once '../header_cdn.php'; ?>
    <title>Alumni Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <style>
        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }
    </style>
</head>
<body class="bg-gray-100 overflow-hidden">
    <?php include '../header.php'; ?>
    <div class="flex h-screen">
        <?php include '../sidebar.php'; ?>

        <div class="flex-1 p-4 md:p-6 overflow-y-auto">
            <h2 class="text-2xl font-bold mb-6">ALUMNI DASHBOARD</h2>

            <?php if ($isAdmin || $isSuperAdmin): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h3 class="font-semibold mb-2">Total Alumni</h3>
                        <p class="text-3xl font-bold text-blue-600"><?php echo htmlspecialchars($total_alumni); ?></p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h3 class="font-semibold mb-2">Total User Registrations</h3>
                        <p class="text-3xl font-bold text-green-600"><?php echo htmlspecialchars($total_registration); ?></p>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-lg shadow mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold">User Registration Analysis</h3>
                        <div class="space-x-2">
                            <button onclick="switchChartType('pie')" class="bg-blue-500 text-white px-3 py-1 rounded">Pie Chart</button>
                            <button onclick="switchChartType('bar')" class="bg-green-500 text-white px-3 py-1 rounded">Bar Chart</button>
                            <button onclick="switchChartType('doughnut')" class="bg-purple-500 text-white px-3 py-1 rounded">Doughnut Chart</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="registrationChart"></canvas>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="font-semibold mb-4">Top Batch Distribution</h3>
                    <div class="chart-container">
                        <canvas id="batchDistributionChart"></canvas>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Rest of the existing code remains the same -->
            <form method="POST" class="my-6">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search by name..." 
                    value="<?php echo htmlspecialchars($search); ?>" 
                    class="border rounded p-2 w-full md:w-1/2"
                />
                <button type="submit" class="bg-blue-500 text-white rounded p-2 mt-2">Search</button>
            </form>

            <!-- User list grid -->
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

                            <?php if ($isAdmin && $row['user_type'] != '2' && $row['user_type'] != '3'): ?>
                                <button class="relative group bg-green-500 text-white rounded-full p-2 mt-2 hover:bg-green-600 transition"
                                        onclick="setAdmin(<?php echo htmlspecialchars($row['pID']); ?>)">
                                    <i class="fas fa-user-shield"></i>
                                    <span class="absolute left-1/2 -translate-x-1/2 bottom-full mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        Set Admin
                                    </span>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500">No users found.</p>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                    <a href="?page=<?php echo $page; ?>&search=<?php echo urlencode($search); ?>" class="bg-blue-500 text-white rounded px-3 py-1 <?php echo ($page == $current_page) ? 'font-bold' : ''; ?>">
                        <?php echo $page; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <script>
        const registrationData = <?php echo json_encode($registration_data); ?>;
        const batchData = <?php echo json_encode($batch_data); ?>;
        const registrationColors = [
            'rgba(75, 192, 192, 0.6)', 
            'rgba(255, 99, 132, 0.6)', 
            'rgba(54, 162, 235, 0.6)', 
            'rgba(255, 206, 86, 0.6)',
            'rgba(153, 102, 255, 0.6)'
        ];

        let registrationChart;
        let batchDistributionChart;

        function initCharts() {
            const registrationCtx = document.getElementById('registrationChart').getContext('2d');
            const batchCtx = document.getElementById('batchDistributionChart').getContext('2d');

            // Registration Chart
            registrationChart = new Chart(registrationCtx, {
                type: 'pie',
                data: {
                    labels: Object.keys(registrationData),
                    datasets: [{
                        data: Object.values(registrationData),
                        backgroundColor: registrationColors
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        datalabels: {
                            color: '#fff',
                            formatter: (value, ctx) => {
                                let sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                let percentage = (value * 100 / sum).toFixed(1) + '%';
                                return percentage;
                            }
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });

            // Batch Distribution Chart
            batchDistributionChart = new Chart(batchCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(batchData),
                    datasets: [{
                        label: 'Batch Count',
                        data: Object.values(batchData),
                        backgroundColor: registrationColors
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        datalabels: {
                            color: '#000',
                            anchor: 'end',
                            align: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }

        function switchChartType(type) {
            if (registrationChart) {
                registrationChart.destroy();
            }

            const registrationCtx = document.getElementById('registrationChart').getContext('2d');
            registrationChart = new Chart(registrationCtx, {
                type: type,
                data: {
                    labels: Object.keys(registrationData),
                    datasets: [{
                        data: Object.values(registrationData),
                        backgroundColor: registrationColors
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        datalabels: {
                            color: '#fff',
                            formatter: (value, ctx) => {
                                let sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                let percentage = (value * 100 / sum).toFixed(1) + '%';
                                return percentage;
                            }
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }

        document.addEventListener('DOMContentLoaded', initCharts);

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
                    const formData = new FormData();
                    formData.append('userID', userID); 
                    formData.append('action', 'set_admin');

                    fetch('../dashboard/query/admin_action.php', {
                        method: 'POST',
                        body: formData
                    }).then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Success!', 'User has been granted admin privileges.', 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', data.message, 'error');
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>