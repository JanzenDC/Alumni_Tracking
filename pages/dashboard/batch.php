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
$userId = $user['id'];
$type = $user['user_type']; // User type (assuming 2 is admin)

// Pagination setup
$limit = 5; // Number of batches per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch batches the user is currently part of
$userBatchesQuery = "SELECT b.batchID, b.batch_name, b.batch_date, b.cover_photo,
    (SELECT COUNT(*) FROM nx_user_batches ub WHERE ub.batchID = b.batchID AND ub.is_active = 1) AS member_count
    FROM nx_batches b
    JOIN nx_user_batches ub ON b.batchID = ub.batchID
    WHERE ub.pID = $userId AND ub.is_active = 1
    LIMIT $limit OFFSET $offset";

$userBatchesResult = $conn->query($userBatchesQuery);

if ($userBatchesResult === false) {
    die("Error executing query: " . $conn->error);
}

$userBatches = [];
if ($userBatchesResult->num_rows > 0) {
    while ($row = $userBatchesResult->fetch_assoc()) {
        $userBatches[] = $row;
    }
}

// Fetch all available batches for joining
$availableBatchesQuery = "SELECT b.batchID, b.batch_name, b.batch_date, b.cover_photo,
    (SELECT COUNT(*) FROM nx_user_batches ub WHERE ub.batchID = b.batchID AND ub.is_active = 1) AS member_count
    FROM nx_batches b
    LEFT JOIN nx_user_batches ub ON b.batchID = ub.batchID AND ub.pID = $userId AND ub.is_active = 1
    WHERE ub.user_batchID IS NULL OR ub.pID != $userId
    LIMIT $limit OFFSET $offset";

$availableBatchesResult = $conn->query($availableBatchesQuery);

if ($availableBatchesResult === false) {
    die("Error executing query: " . $conn->error);
}

$availableBatches = [];
if ($availableBatchesResult->num_rows > 0) {
    while ($row = $availableBatchesResult->fetch_assoc()) {
        $availableBatches[] = $row;
    }
}

// Count total available batches for pagination
$count_query = "SELECT COUNT(*) as total FROM nx_batches";
$count_result = $conn->query($count_query);

if ($count_result === false) {
    die("Error executing count query: " . $conn->error);
}

$total_batches = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_batches / $limit);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once '../header_cdn.php'; ?>
    <title>User Dashboard</title>
</head>
<body class="bg-gray-100 h-screen overflow-hidden">
    <?php include '../header.php';  // Include the header ?>
    <div class="flex h-screen">
        <?php include '../sidebar.php';  // Include the sidebar ?>

        <div class="container mx-auto px-4 py-8 overflow-y-auto">
            <div class="mb-6">
                <?php if (!empty($userBatches)): ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <?php foreach ($userBatches as $batch): ?>
                            <div class="bg-white rounded-xl shadow-lg overflow-hidden transition-transform duration-300 hover:scale-105">
                                <div class="relative h-48 bg-gray-200">
                                    <?php if (!empty($batch['cover_photo'])): ?>
                                        <img src="../../images/batch_group_images/<?php echo htmlspecialchars($batch['cover_photo']); ?>" alt="<?php echo htmlspecialchars($batch['batch_name']); ?>" class="w-full h-full object-cover" />
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-400 to-blue-600">
                                            <span class="text-4xl font-bold text-white"><?php echo date('Y', strtotime($batch['batch_date'])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="absolute top-2 right-2 bg-white rounded-full px-3 py-1 text-sm font-semibold text-gray-700 shadow">
                                        <?php echo htmlspecialchars($batch['member_count']); ?> member(s)
                                    </div>
                                </div>
                                <div class="p-4">
                                    <h3 class="text-xl font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($batch['batch_name']); ?></h3>
                                    <p class="text-sm text-gray-600 mb-4">Start Date: <?php echo date('M d, Y', strtotime($batch['batch_date'])); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No batches joined yet.</p>
                <?php endif; ?>
            </div>

            <!-- Admin button to create new batch -->
            <?php if ($type == 2 || $type == 3): // Admin ?>
                <div class="mb-6">
                    <button onclick="openModal()" class="px-4 py-2 bg-blue-500 text-white rounded-lg font-semibold hover:bg-blue-600">
                        Create New Batch
                    </button>
                </div>
            <?php endif; ?>

            <div class="mb-6">
                <div class="md:flex md:justify-between">
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">Explore Batches</h2>
                    <div class="flex justify-center mt-6">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="px-4 py-2 border rounded bg-blue-500 text-white hover:bg-blue-600">Previous</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" class="px-4 py-2 border rounded <?php echo $i === $page ? 'bg-blue-500 text-white' : 'bg-white text-blue-500'; ?> hover:bg-blue-600 hover:text-white">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="px-4 py-2 border rounded bg-blue-500 text-white hover:bg-blue-600">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($availableBatches)): ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <?php foreach ($availableBatches as $batch): ?>
                            <div class="bg-white rounded-xl shadow-lg overflow-hidden transition-transform duration-300 hover:scale-105">
                                <div class="relative h-48 bg-gray-200">
                                    <?php if (!empty($batch['cover_photo'])): ?>
                                        <img src="../../images/batch_group_images/<?php echo htmlspecialchars($batch['cover_photo']); ?>" alt="<?php echo htmlspecialchars($batch['batch_name']); ?>" class="w-full h-full object-cover" />
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-400 to-blue-600">
                                            <span class="text-4xl font-bold text-white"><?php echo date('Y', strtotime($batch['batch_date'])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="absolute top-2 right-2 bg-white rounded-full px-3 py-1 text-sm font-semibold text-gray-700 shadow">
                                        <?php echo htmlspecialchars($batch['member_count']); ?> member(s)
                                    </div>
                                </div>
                                <div class="p-4">
                                    <h3 class="text-xl font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($batch['batch_name']); ?></h3>
                                    <p class="text-sm text-gray-600 mb-4">Start Date: <?php echo date('M d, Y', strtotime($batch['batch_date'])); ?></p>
                                    <button onclick="joinBatch(<?php echo $batch['batchID']; ?>)" class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg font-semibold hover:bg-blue-600 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                                        Join Now
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No batches available to join.</p>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- Modal for Creating New Batch -->
    <div id="createBatchModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg p-6 w-11/12 md:w-1/3">
            <h2 class="text-2xl font-semibold mb-4">Create New Batch</h2>
            <form id="createBatchForm" onsubmit="createBatch(event)" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="batch_name" class="block text-sm font-medium text-gray-700">Batch Name</label>
                    <input type="text" id="batch_name" name="batch_name" required class="border rounded p-2 w-full">
                </div>
                <div class="mb-4">
                    <label for="batch_date" class="block text-sm font-medium text-gray-700">Batch Date</label>
                    <input type="datetime-local" id="batch_date" name="batch_date" required class="border rounded p-2 w-full">
                </div>
                <div class="mb-4">
                    <label for="profile" class="block text-sm font-medium text-gray-700">Profile Info</label>
                    <input type="text" id="profile" name="profile" class="border rounded p-2 w-full">
                </div>
                <div class="mb-4">
                    <label for="cover_photo" class="block text-sm font-medium text-gray-700">Cover Photo (required)</label>
                    <input type="file" id="cover_photo" name="cover_photo" required accept="image/*" class="border rounded p-2 w-full">
                </div>
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" name="description" class="border rounded p-2 w-full"></textarea>
                </div>
                <div class="flex justify-between">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg">Create Batch</button>
                </div>
            </form>
        </div>
    </div>


    <script>
    function openModal() {
        document.getElementById('createBatchModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('createBatchModal').classList.add('hidden');
    }

    function createBatch(event) {
    event.preventDefault(); // Prevent the default form submission

    const formData = new FormData(document.getElementById('createBatchForm'));

    fetch('create_batch.php', {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Batch Created',
                text: data.message,
            });
            closeModal(); // Close the modal
            // Optionally refresh the page or update the UI
            setTimeout(() => location.reload(), 2000);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while creating the batch.',
        });
    });
}


    function joinBatch(batchId) {
        fetch('../dashboard/query/join_batch.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'batchId=' + batchId
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
            console.error('Error:', error);
            toastr.error('An error occurred while joining the batch.');
        });
    }

    </script>

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
