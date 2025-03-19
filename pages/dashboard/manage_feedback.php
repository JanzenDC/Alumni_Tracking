<?php
session_start();
require_once '../../backend/db_connect.php';

// Ensure the user is logged in and has admin privileges
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit;
}

$user = $_SESSION['user'];
$userId = $user['id'];

// Handle status updates if requested
if (isset($_POST['update_status'])) {
    $feedbackId = $conn->real_escape_string($_POST['feedback_id']);
    $newStatus = $conn->real_escape_string($_POST['status']);
    
    $updateSql = "UPDATE nx_feedback SET status = '$newStatus' WHERE feedback_id = '$feedbackId'";
    $conn->query($updateSql);
}

// Pagination settings
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Filtering options
$statusFilter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';
$ratingFilter = isset($_GET['rating']) ? intval($_GET['rating']) : 0;
$searchTerm = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Build the query with filters
$whereClause = [];
if ($statusFilter) {
    $whereClause[] = "f.status = '$statusFilter'";
}
if ($ratingFilter > 0) {
    $whereClause[] = "f.rating = $ratingFilter";
}
if ($searchTerm) {
    $whereClause[] = "(f.subject LIKE '%$searchTerm%' OR f.message LIKE '%$searchTerm%')";
}

$whereStr = !empty($whereClause) ? "WHERE " . implode(" AND ", $whereClause) : "";

// Fetch total count for pagination
$countSql = "SELECT COUNT(*) as total FROM nx_feedback f $whereStr";
$countResult = $conn->query($countSql);
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Fetch all feedback with user information
$sql = "SELECT f.*, u.username, u.fname, u.lname, u.email
        FROM nx_feedback f
        LEFT JOIN nx_users u ON f.user_id = u.pID
        $whereStr
        ORDER BY f.created_at DESC
        LIMIT $offset, $limit";
$result = $conn->query($sql);

// Get unique statuses for filter dropdown
$statusSql = "SELECT DISTINCT status FROM nx_feedback";
$statusResult = $conn->query($statusSql);
$statuses = [];
while ($row = $statusResult->fetch_assoc()) {
    $statuses[] = $row['status'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php include_once '../header_cdn.php'; ?>
  <title>All Feedback</title>
</head>
<body class="bg-gray-100 h-screen overflow-hidden">
  <?php include '../header.php'; ?>
  <div class="flex h-screen">
    <?php include '../sidebar.php'; ?>
    <div class="container mx-auto px-4 py-8 overflow-y-auto mb-16">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-800">All User Feedback</h2>
        <div class="text-sm text-gray-600">
          Total: <?php echo $totalRows; ?> feedback entries
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" class="flex flex-wrap items-center gap-4">
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select id="status" name="status" class="border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">All Statuses</option>
              <?php foreach($statuses as $status): ?>
                <option value="<?php echo $status; ?>" <?php echo ($statusFilter == $status) ? 'selected' : ''; ?>>
                  <?php echo ucfirst($status); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label for="rating" class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
            <select id="rating" name="rating" class="border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="0">All Ratings</option>
              <?php for($i = 1; $i <= 5; $i++): ?>
                <option value="<?php echo $i; ?>" <?php echo ($ratingFilter == $i) ? 'selected' : ''; ?>>
                  <?php echo $i; ?> Star<?php echo ($i > 1) ? 's' : ''; ?>
                </option>
              <?php endfor; ?>
            </select>
          </div>
          <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Search subject or content" class="border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div class="self-end">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-200">Apply Filters</button>
            <a href="manage_feedback.php" class="ml-2 text-blue-500 hover:underline">Clear</a>
          </div>
        </form>
      </div>

      <!-- Feedback Table -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <?php if ($result->num_rows > 0): ?>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Feedback</th>
                  <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                  <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                  <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <?php while($row = $result->fetch_assoc()): ?>
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div>
                          <div class="text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?>
                          </div>
                          <div class="text-sm text-gray-500">
                            <?php echo htmlspecialchars($row['email']); ?>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="text-sm text-gray-900"><?php echo htmlspecialchars($row['subject']); ?></div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="text-sm text-gray-900 max-w-xs truncate" title="<?php echo htmlspecialchars($row['message']); ?>">
                        <?php echo htmlspecialchars(substr($row['message'], 0, 100)) . (strlen($row['message']) > 100 ? '...' : ''); ?>
                      </div>
                      <button onclick="viewFeedbackDetails(<?php echo $row['feedback_id']; ?>, '<?php echo addslashes($row['subject']); ?>', '<?php echo addslashes($row['message']); ?>')" 
                              class="text-xs text-blue-500 hover:text-blue-700">
                        View Full Message
                      </button>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                      <div class="flex items-center justify-center">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                          <span class="text-yellow-400">
                            <?php echo ($i <= $row['rating']) ? '★' : '☆'; ?>
                          </span>
                        <?php endfor; ?>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                      <form method="POST" id="status-form-<?php echo $row['feedback_id']; ?>" class="inline-block">
                        <input type="hidden" name="update_status" value="1">
                        <input type="hidden" name="feedback_id" value="<?php echo $row['feedback_id']; ?>">
                        <select name="status" onchange="document.getElementById('status-form-<?php echo $row['feedback_id']; ?>').submit();" 
                                class="text-sm rounded px-2 py-1 border 
                                <?php 
                                if ($row['status'] == 'pending') echo 'bg-yellow-100 border-yellow-300';
                                elseif ($row['status'] == 'reviewed') echo 'bg-blue-100 border-blue-300';
                                else echo 'bg-green-100 border-green-300';
                                ?>">
                          <option value="pending" <?php echo ($row['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                          <option value="reviewed" <?php echo ($row['status'] == 'reviewed') ? 'selected' : ''; ?>>Reviewed</option>
                          <option value="addressed" <?php echo ($row['status'] == 'addressed') ? 'selected' : ''; ?>>Addressed</option>
                        </select>
                      </form>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                      <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                      <div class="text-xs"><?php echo date('h:i A', strtotime($row['created_at'])); ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                      <a href="mailto:<?php echo $row['email']; ?>" class="text-blue-500 hover:text-blue-700 mr-3" title="Reply to User">
                        <i class="fas fa-reply"></i>
                      </a>
                      <button onclick="deleteFeedback(<?php echo $row['feedback_id']; ?>)" class="text-red-500 hover:text-red-700" title="Delete Feedback">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
          
          <!-- Pagination -->
          <?php if ($totalPages > 1): ?>
            <div class="px-6 py-4 bg-gray-50 border-t flex justify-between items-center">
              <div>
                Page <?php echo $page; ?> of <?php echo $totalPages; ?>
              </div>
              <div class="flex space-x-2">
                <?php if ($page > 1): ?>
                  <a href="?page=<?php echo ($page - 1); ?>&status=<?php echo $statusFilter; ?>&rating=<?php echo $ratingFilter; ?>&search=<?php echo $searchTerm; ?>" 
                     class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                    Previous
                  </a>
                <?php endif; ?>
                
                <?php if ($page < $totalPages): ?>
                  <a href="?page=<?php echo ($page + 1); ?>&status=<?php echo $statusFilter; ?>&rating=<?php echo $ratingFilter; ?>&search=<?php echo $searchTerm; ?>" 
                     class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Next
                  </a>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>
          
        <?php else: ?>
          <div class="p-6 text-center text-gray-500">
            No feedback found matching your criteria.
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- View Feedback Details Modal -->
  <div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-lg">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold" id="viewModalTitle"></h3>
        <button onclick="closeViewModal()" class="text-gray-500 hover:text-gray-700">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="mb-6">
        <p id="viewModalContent" class="text-gray-700 whitespace-pre-wrap"></p>
      </div>
      <div class="flex justify-end">
        <button onclick="closeViewModal()" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400 transition duration-200">
          Close
        </button>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
      <h3 class="text-xl font-semibold mb-4">Confirm Delete</h3>
      <p class="mb-6">Are you sure you want to delete this feedback? This action cannot be undone.</p>
      <form action="delete_feedback.php" method="POST">
        <input type="hidden" id="delete_feedback_id" name="feedback_id">
        <div class="flex justify-end">
          <button type="button" onclick="closeDeleteModal()" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400 transition duration-200 mr-2">Cancel</button>
          <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition duration-200">Delete</button>
        </div>
      </form>
    </div>
  </div>

  <!-- JavaScript -->
  <script>
    // View feedback details
    function viewFeedbackDetails(id, subject, message) {
      document.getElementById('viewModalTitle').textContent = subject;
      document.getElementById('viewModalContent').textContent = message;
      
      document.getElementById('viewModal').classList.remove('hidden');
      document.getElementById('viewModal').classList.add('flex');
    }

    function closeViewModal() {
      document.getElementById('viewModal').classList.remove('flex');
      document.getElementById('viewModal').classList.add('hidden');
    }

    // Delete feedback
    function deleteFeedback(id) {
      document.getElementById('delete_feedback_id').value = id;
      
      document.getElementById('deleteModal').classList.remove('hidden');
      document.getElementById('deleteModal').classList.add('flex');
    }

    function closeDeleteModal() {
      document.getElementById('deleteModal').classList.remove('flex');
      document.getElementById('deleteModal').classList.add('hidden');
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
      const viewModal = document.getElementById('viewModal');
      const deleteModal = document.getElementById('deleteModal');
      
      if (event.target === viewModal) {
        closeViewModal();
      }
      
      if (event.target === deleteModal) {
        closeDeleteModal();
      }
    }
  </script>
</body>
</html>