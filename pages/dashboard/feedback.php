<?php
session_start();
require_once '../../backend/db_connect.php';

// Ensure the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit;
}

$user = $_SESSION['user'];
$userId = $user['id'];
$type = $user['user_type'];

// Process feedback submission
$message = '';
$alertClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            // Create new feedback
            $subject = $conn->real_escape_string($_POST['subject']);
            $feedback = $conn->real_escape_string($_POST['feedback']);
            $rating = $conn->real_escape_string($_POST['rating']);

            $sql = "INSERT INTO nx_feedback (user_id, subject, message, rating) VALUES ('$userId', '$subject', '$feedback', '$rating')";
            
            if ($conn->query($sql)) {
                $message = "Feedback submitted successfully!";
                $alertClass = "alert-success";
            } else {
                $message = "Error: " . $conn->error;
                $alertClass = "alert-danger";
            }
        } else if ($_POST['action'] === 'edit') {
            // Edit existing feedback
            $feedbackId = $conn->real_escape_string($_POST['feedback_id']);
            $subject = $conn->real_escape_string($_POST['subject']);
            $feedback = $conn->real_escape_string($_POST['feedback']);
            $rating = $conn->real_escape_string($_POST['rating']);

            // Verify that this feedback belongs to the current user
            $checkSql = "SELECT * FROM nx_feedback WHERE feedback_id = '$feedbackId' AND user_id = '$userId'";
            $checkResult = $conn->query($checkSql);
            
            if ($checkResult->num_rows > 0) {
                $sql = "UPDATE nx_feedback SET subject = '$subject', message = '$feedback', rating = '$rating', updated_at = CURRENT_TIMESTAMP WHERE feedback_id = '$feedbackId' AND user_id = '$userId'";
                
                if ($conn->query($sql)) {
                    $message = "Feedback updated successfully!";
                    $alertClass = "alert-success";
                } else {
                    $message = "Error: " . $conn->error;
                    $alertClass = "alert-danger";
                }
            } else {
                $message = "You don't have permission to edit this feedback.";
                $alertClass = "alert-danger";
            }
        } else if ($_POST['action'] === 'delete') {
            // Delete feedback
            $feedbackId = $conn->real_escape_string($_POST['feedback_id']);
            
            // Verify that this feedback belongs to the current user
            $checkSql = "SELECT * FROM nx_feedback WHERE feedback_id = '$feedbackId' AND user_id = '$userId'";
            $checkResult = $conn->query($checkSql);
            
            if ($checkResult->num_rows > 0) {
                $sql = "DELETE FROM nx_feedback WHERE feedback_id = '$feedbackId' AND user_id = '$userId'";
                
                if ($conn->query($sql)) {
                    $message = "Feedback deleted successfully!";
                    $alertClass = "alert-success";
                } else {
                    $message = "Error: " . $conn->error;
                    $alertClass = "alert-danger";
                }
            } else {
                $message = "You don't have permission to delete this feedback.";
                $alertClass = "alert-danger";
            }
        }
    }
}

// Get user's feedback entries
$sql = "SELECT * FROM nx_feedback WHERE user_id = '$userId' ORDER BY created_at DESC";
$result = $conn->query($sql);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php include_once '../header_cdn.php'; ?>
  <title>User Feedback</title>
</head>
<body class="bg-gray-100 h-screen overflow-hidden">
  <?php include '../header.php'; ?>
  <div class="flex h-screen">
    <?php include '../sidebar.php'; ?>
    <div class="container mx-auto px-4 py-8 overflow-y-auto mb-16">
      <h2 class="text-3xl font-bold text-gray-800 mb-6">Feedback</h2>

      <?php if ($message): ?>
        <div class="alert <?php echo $alertClass; ?> mb-4">
          <?php echo $message; ?>
        </div>
      <?php endif; ?>

      <!-- Feedback Form -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-xl font-semibold mb-4">Submit New Feedback</h3>
        <form id="feedbackForm" method="POST">
          <input type="hidden" name="action" value="add">
          <div class="mb-4">
            <label for="subject" class="block text-gray-700 font-medium mb-2">Subject</label>
            <input type="text" id="subject" name="subject" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
          </div>
          <div class="mb-4">
            <label for="feedback" class="block text-gray-700 font-medium mb-2">Your Feedback</label>
            <textarea id="feedback" name="feedback" rows="4" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
          </div>
          <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Rating</label>
            <div class="flex items-center">
              <?php for($i = 1; $i <= 5; $i++): ?>
                <label class="mr-4">
                  <input type="radio" name="rating" value="<?php echo $i; ?>" <?php echo ($i == 5) ? 'checked' : ''; ?>>
                  <?php echo $i; ?>
                </label>
              <?php endfor; ?>
            </div>
          </div>
          <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition duration-200">Submit Feedback</button>
        </form>
      </div>

      <!-- User's Feedback List -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-semibold mb-4">Your Previous Feedback</h3>
        
        <?php if ($result->num_rows > 0): ?>
          <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
              <thead>
                <tr>
                  <th class="py-2 px-4 border-b text-left">Subject</th>
                  <th class="py-2 px-4 border-b text-left">Feedback</th>
                  <th class="py-2 px-4 border-b text-center">Rating</th>
                  <th class="py-2 px-4 border-b text-center">Status</th>
                  <th class="py-2 px-4 border-b text-center">Date</th>
                  <th class="py-2 px-4 border-b text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                  <tr>
                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['subject']); ?></td>
                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars(substr($row['message'], 0, 50)) . (strlen($row['message']) > 50 ? '...' : ''); ?></td>
                    <td class="py-2 px-4 border-b text-center"><?php echo $row['rating']; ?>/5</td>
                    <td class="py-2 px-4 border-b text-center">
                      <span class="px-2 py-1 rounded text-xs 
                        <?php 
                        if ($row['status'] == 'pending') echo 'bg-yellow-200 text-yellow-800';
                        elseif ($row['status'] == 'reviewed') echo 'bg-blue-200 text-blue-800';
                        else echo 'bg-green-200 text-green-800';
                        ?>">
                        <?php echo ucfirst($row['status']); ?>
                      </span>
                    </td>
                    <td class="py-2 px-4 border-b text-center"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                    <td class="py-2 px-4 border-b text-center">
                      <button onclick="editFeedback(<?php echo $row['feedback_id']; ?>, '<?php echo addslashes($row['subject']); ?>', '<?php echo addslashes($row['message']); ?>', <?php echo $row['rating']; ?>)" class="text-blue-500 hover:text-blue-700 mr-2">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button onclick="deleteFeedback(<?php echo $row['feedback_id']; ?>)" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p class="text-gray-500">You haven't submitted any feedback yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Edit Feedback Modal -->
  <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
      <h3 class="text-xl font-semibold mb-4">Edit Feedback</h3>
      <form id="editForm" method="POST">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" id="edit_feedback_id" name="feedback_id">
        <div class="mb-4">
          <label for="edit_subject" class="block text-gray-700 font-medium mb-2">Subject</label>
          <input type="text" id="edit_subject" name="subject" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        <div class="mb-4">
          <label for="edit_feedback" class="block text-gray-700 font-medium mb-2">Your Feedback</label>
          <textarea id="edit_feedback" name="feedback" rows="4" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 font-medium mb-2">Rating</label>
          <div class="flex items-center">
            <?php for($i = 1; $i <= 5; $i++): ?>
              <label class="mr-4">
                <input type="radio" name="rating" id="edit_rating_<?php echo $i; ?>" value="<?php echo $i; ?>">
                <?php echo $i; ?>
              </label>
            <?php endfor; ?>
          </div>
        </div>
        <div class="flex justify-end">
          <button type="button" onclick="closeEditModal()" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400 transition duration-200 mr-2">Cancel</button>
          <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition duration-200">Update</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
      <h3 class="text-xl font-semibold mb-4">Confirm Delete</h3>
      <p class="mb-6">Are you sure you want to delete this feedback? This action cannot be undone.</p>
      <form id="deleteForm" method="POST">
        <input type="hidden" name="action" value="delete">
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
    // Edit feedback functions
    function editFeedback(id, subject, message, rating) {
      document.getElementById('edit_feedback_id').value = id;
      document.getElementById('edit_subject').value = subject;
      document.getElementById('edit_feedback').value = message;
      document.getElementById('edit_rating_' + rating).checked = true;
      
      document.getElementById('editModal').classList.remove('hidden');
      document.getElementById('editModal').classList.add('flex');
    }

    function closeEditModal() {
      document.getElementById('editModal').classList.remove('flex');
      document.getElementById('editModal').classList.add('hidden');
    }

    // Delete feedback functions
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
      const editModal = document.getElementById('editModal');
      const deleteModal = document.getElementById('deleteModal');
      
      if (event.target === editModal) {
        closeEditModal();
      }
      
      if (event.target === deleteModal) {
        closeDeleteModal();
      }
    }
  </script>
</body>
</html>