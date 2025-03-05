<?php
session_start();
require_once '../../backend/db_connect.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit;
}

$user = $_SESSION['user'];
$isAdmin = $user['user_type'] === '2' || $user['user_type'] === '3';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$query = "SELECT * FROM nx_job_postings LIMIT $limit OFFSET $offset";
$result = $conn->query($query);

$totalJobsQuery = "SELECT COUNT(*) as total FROM nx_job_postings";
$totalResult = $conn->query($totalJobsQuery);
$totalJobs = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalJobs / $limit);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once '../header_cdn.php'; ?>
    <title>User Dashboard</title>
    <style>
        @keyframes blink {
            50% { opacity: 0.3; }
        }
        .blinking-pin {
            color: red;
            animation: blink 1s infinite;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include '../header.php'; ?>
    <div class="flex h-screen">
        <?php include '../sidebar.php'; ?>

        <div class="flex-1 p-4 md:p-6 overflow-y-auto mb-16">
            <h2 class="text-2xl font-bold mb-6">
                Job Listings 
            </h2>
            
            <?php if ($isAdmin): ?>
                <button onclick="openCreateJobModal()" class="px-4 py-2 bg-green-500 text-white rounded-lg mb-4">Create New Job</button>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($job = $result->fetch_assoc()): ?>
                        <div class="bg-yellow-100 p-4 rounded-lg shadow hover:shadow-lg transition-shadow">
                            <h3 class="text-xl font-semibold mb-2"><i class="fas fa-thumbtack blinking-pin"></i> <?php echo htmlspecialchars($job['title']); ?></h3>
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($job['description']); ?></p>
                            <p class="text-gray-500 text-sm">Posted on: <?php echo date('Y-m-d h:i A', strtotime($job['created_at'])); ?></p>
                            <?php if ($isAdmin): ?>
                                <button onclick="confirmDelete(<?php echo $job['jobID']; ?>)" class="text-red-500 hover:underline mt-4">Delete</button>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-gray-500">No job listings available.</p>
                <?php endif; ?>
            </div>

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

    <div id="createJobModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg p-6 w-11/12 md:w-1/3">
            <h2 class="text-2xl font-semibold mb-4">Create New Job</h2>
            <form id="createJobForm" onsubmit="createJob(event)">
                <div class="mb-4">
                    <label for="job_title" class="block text-sm font-medium text-gray-700">Job Title</label>
                    <input type="text" id="job_title" name="job_title" required class="border rounded p-2 w-full">
                </div>
                <div class="mb-4">
                    <label for="job_description" class="block text-sm font-medium text-gray-700">Job Description</label>
                    <textarea id="job_description" name="job_description" required class="border rounded p-2 w-full"></textarea>
                </div>
                <div class="flex justify-between">
                    <button type="button" onclick="closeCreateJobModal()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg">Create Job</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateJobModal() {
            document.getElementById('createJobModal').classList.remove('hidden');
        }

        function closeCreateJobModal() {
            document.getElementById('createJobModal').classList.add('hidden');
        }

        function createJob(event) {
            event.preventDefault();

            const formData = new FormData(document.getElementById('createJobForm'));
            formData.append('posted_by', <?php echo json_encode($user['id']); ?>);

            fetch('../dashboard/query/create_job.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    setTimeout(() => location.reload(), 2000);
                } else {
                    toastr.error(data.message);
                }
            })
            .catch(error => {
                toastr.error('An error occurred while creating the job.');
            });
        }

        function confirmDelete(jobId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteJob(jobId);
                }
            });
        }

        function deleteJob(jobId) {
            fetch('../dashboard/query/delete_job.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ jobID: jobId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    setTimeout(() => location.reload(), 2000);
                } else {
                    toastr.error(data.message);
                }
            })
            .catch(error => {
                toastr.error('An error occurred while deleting the job.');
            });
        }
    </script>

    <?php if (isset($_SESSION['toastr_message'])): ?>
        <script>
            $(document).ready(function() {
                toastr.<?php echo $_SESSION['toastr_type']; ?>('<?php echo $_SESSION['toastr_message']; ?>');
                <?php unset($_SESSION['toastr_message'], $_SESSION['toastr_type']); ?>
            });
        </script>
    <?php endif; ?>
</body>
</html>
