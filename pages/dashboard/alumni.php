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

// Count Admins
$adminQuery = "SELECT COUNT(*) AS admin_count FROM nx_users WHERE pID IN (SELECT pID FROM nx_user_type WHERE type = 2)";
$adminResult = mysqli_query($conn, $adminQuery);
$adminCount = mysqli_fetch_assoc($adminResult)['admin_count'];

// Count Registered Alumni
$alumniQuery = "SELECT COUNT(*) AS alumni_count FROM nx_users";
$alumniResult = mysqli_query($conn, $alumniQuery);
$alumniCount = mysqli_fetch_assoc($alumniResult)['alumni_count'];

// User Distribution by Batch
$batchQuery = "SELECT b.batch_name, COUNT(ub.pID) as user_count 
               FROM nx_batches b
               LEFT JOIN nx_user_batches ub ON b.batchID = ub.batchID
               GROUP BY b.batch_name
               ORDER BY user_count DESC";
$batchResult = mysqli_query($conn, $batchQuery);

$batchLabels = [];
$batchCounts = [];
while ($row = mysqli_fetch_assoc($batchResult)) {
    $batchLabels[] = $row['batch_name'];
    $batchCounts[] = $row['user_count'];
}

// User Type Distribution
$userTypeQuery = "SELECT 
    CASE 
        WHEN type = 3 THEN 'Super Admin'
        WHEN type = 2 THEN 'Admin'
        ELSE 'Regular User'
    END as user_type, 
    COUNT(*) as type_count 
FROM nx_users u
LEFT JOIN nx_user_type ut ON u.pID = ut.pID
GROUP BY user_type";
$userTypeResult = mysqli_query($conn, $userTypeQuery);

$userTypeLabels = [];
$userTypeCounts = [];
while ($row = mysqli_fetch_assoc($userTypeResult)) {
    $userTypeLabels[] = $row['user_type'];
    $userTypeCounts[] = $row['type_count'];
}

// Fetch user data
$query = "SELECT 
    u.pID, 
    u.username, 
    u.email, 
    u.fname, 
    u.lname, 
    ut.type, 
    b.batch_name, 
    ub.is_active, 
    ub.joined_at 
FROM nx_users u
LEFT JOIN nx_user_type ut ON u.pID = ut.pID
LEFT JOIN nx_user_batches ub ON u.pID = ub.pID
LEFT JOIN nx_batches b ON ub.batchID = b.batchID
GROUP BY u.pID";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once '../header_cdn.php'; ?>
    <title>Alumni Dashboard</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    
    <style>
        .dashboard-card {
            background: #fde2e4;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            color: #333;
            font-size: 18px;
            font-weight: bold;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
        }
        .dashboard-card.admin {
            background: #bde0fe;
        }
        .dashboard-card.alumni {
            background: #ffcad4;
        }
        .dashboard-card i {
            font-size: 30px;
        }
        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }
        .chart-type-buttons {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
        }
        .chart-type-buttons button {
            margin: 0 10px;
            padding: 5px 10px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .chart-type-buttons button.active {
            background-color: #2c3e50;
        }
    </style>
</head>
<body class="bg-gray-100 overflow-hidden">
    <?php include '../header.php'; ?>
    <div class="flex h-screen">
        <?php include '../sidebar.php'; ?>

        <div class="flex-1 p-4 md:p-6 overflow-y-auto">
            <h2 class="text-2xl font-bold mb-6">ALUMNI DASHBOARD</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="dashboard-card admin">
                    <span><i class="fas fa-user-shield"></i> Admins</span>
                    <span><?php echo $adminCount; ?></span>
                </div>
                <div class="dashboard-card alumni">
                    <span><i class="fas fa-users"></i> Registered Alumni</span>
                    <span><?php echo $alumniCount; ?></span>
                </div>
            </div>

            <div class="bg-white shadow-md rounded-lg p-4 mb-4">
                <div class="chart-type-buttons">
                    <button onclick="switchChartType('batch')" class="active" id="batchChartBtn">Batch Distribution</button>
                    <button onclick="switchChartType('userType')" id="userTypeChartBtn">User Type Distribution</button>
                </div>
                <div class="chart-container">
                    <canvas id="dashboardChart"></canvas>
                </div>
            </div>

            <div class="bg-white shadow-md rounded-lg p-4">
                <table id="alumniTable" class="display responsive nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>User Type</th>
                            <th>Batch</th>
                            <th>Active</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                            <tr>
                                <td><?php echo $row['pID']; ?></td>
                                <td><?php echo $row['username']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td><?php echo $row['fname']; ?></td>
                                <td><?php echo $row['lname']; ?></td>
                                <td>
                                    <?php 
                                    if (!isset($row['type'])) {
                                        echo "User"; 
                                    } else {
                                        if ((int)$row['type'] === 3) {
                                            echo "Super Admin";
                                        } elseif ((int)$row['type'] === 2) {
                                            echo "Admin";
                                        } else {
                                            echo "User";
                                        }
                                    }
                                    ?>
                                </td>
                                <td><?php echo isset($row['batch_name']) ? $row['batch_name'] : 'No'; ?></td>
                                <td><?php echo isset($row['is_active']) && $row['is_active'] ? 'Yes' : 'No'; ?></td>
                                <td><?php echo $row['joined_at']; ?></td>
                                <td>
                                    <?php if ($isAdmin || $isSuperAdmin) { ?>
                                        <?php if (!isset($row['type']) || $row['type'] != 2) { ?>
                                            <button onclick="setAdmin(<?php echo $row['pID']; ?>)" class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600">Make Admin</button>
                                        <?php } else { ?>
                                            <button onclick="removeAdmin(<?php echo $row['pID']; ?>)" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">Remove Admin</button>
                                        <?php } ?>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Chart.js Data
        const batchData = {
            labels: <?php echo json_encode($batchLabels); ?>,
            datasets: [{
                label: 'Alumni per Batch',
                data: <?php echo json_encode($batchCounts); ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(153, 102, 255, 0.6)'
                ]
            }]
        };

        const userTypeData = {
            labels: <?php echo json_encode($userTypeLabels); ?>,
            datasets: [{
                label: 'User Type Distribution',
                data: <?php echo json_encode($userTypeCounts); ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 206, 86, 0.6)'
                ]
            }]
        };

        let currentChart = null;
        let currentChartType = 'bar';
        let currentDataset = 'batch';

        function createChart(chartType, data) {
            const ctx = document.getElementById('dashboardChart').getContext('2d');
            
            // Destroy existing chart if it exists
            if (currentChart) {
                currentChart.destroy();
            }

            currentChart = new Chart(ctx, {
                type: chartType,
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        datalabels: {
                            color: 'white',
                            font: {
                                weight: 'bold'
                            },
                            formatter: (value) => value
                        },
                        legend: {
                            position: 'top'
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }

        function switchChartType(dataset) {
            // Reset active buttons
            document.getElementById('batchChartBtn').classList.remove('active');
            document.getElementById('userTypeChartBtn').classList.remove('active');

            // Set active button for current dataset
            document.getElementById(dataset + 'ChartBtn').classList.add('active');

            // Switch dataset
            currentDataset = dataset;
            const data = dataset === 'batch' ? batchData : userTypeData;

            // Cycle through chart types
            const chartTypes = ['bar', 'pie', 'doughnut'];
            const currentIndex = chartTypes.indexOf(currentChartType);
            const nextIndex = (currentIndex + 1) % chartTypes.length;
            currentChartType = chartTypes[nextIndex];

            createChart(currentChartType, data);
        }

        // Initialize the chart on page load
        $(document).ready(function() {
            $('#alumniTable').DataTable({
                responsive: true
            });

            // Create initial batch chart
            createChart('bar', batchData);
        });

        function removeAdmin(userID) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to remove this user as an admin?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, remove admin!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../dashboard/query/admin_action.php', {
                        method: 'POST',
                        body: JSON.stringify({ userID: userID, action: 'remove_admin' }),
                        headers: { 'Content-Type': 'application/json' }
                    }).then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Success!', 'User has been removed as an admin.', 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error!', data.message, 'error');
                        }
                    });
                }
            });
        }

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
                    fetch('../dashboard/query/admin_action.php', {
                        method: 'POST',
                        body: JSON.stringify({ userID: userID, action: 'set_admin' }),
                        headers: { 'Content-Type': 'application/json' }
                    }).then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Success!', 'User has been granted admin privileges.', 'success').then(() => location.reload());
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
