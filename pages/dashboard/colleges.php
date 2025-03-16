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

// Process AJAX actions if sent via POST
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    
    if ($action === 'join_department') {
        $dept_id = isset($_POST['dept_id']) ? intval($_POST['dept_id']) : 0;
        
        if ($dept_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid department ID.']);
            exit;
        }
        
        // Get department name
        $deptQuery = $conn->prepare("SELECT dept_name FROM college_departments WHERE dept_id = ?");
        $deptQuery->bind_param("i", $dept_id);
        $deptQuery->execute();
        $deptResult = $deptQuery->get_result();
        
        if ($deptResult->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Department not found.']);
            exit;
        }
        
        $deptRow = $deptResult->fetch_assoc();
        $deptName = $deptRow['dept_name'];
        $deptQuery->close();
        
        // Update user's college_department field
        $updateUserStmt = $conn->prepare("UPDATE nx_users SET college_department = ? WHERE pID = ?");
        $updateUserStmt->bind_param("si", $deptName, $userId);
        
        if ($updateUserStmt->execute()) {
            // Update the session to reflect the change
            $_SESSION['user']['college_department'] = $deptName;
            echo json_encode(['success' => true, 'message' => 'You have successfully joined the ' . $deptName . ' department.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to join department: ' . $conn->error]);
        }
        $updateUserStmt->close();
        exit;
    } elseif ($action === 'create') {
        // Only admin users can create departments
        if ($type != 2 && $type != 3) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized action.']);
            exit;
        }
        
        $dept_name = isset($_POST['dept_name']) ? trim($_POST['dept_name']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        
        if (empty($dept_name)) {
            echo json_encode(['success' => false, 'message' => 'Department name is required.']);
            exit;
        }
        
        $stmt = $conn->prepare("INSERT INTO college_departments (dept_name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $dept_name, $description);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Department created successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create department.']);
        }
        $stmt->close();
        exit;
    } elseif ($action === 'edit') {
        // Only admin users can edit departments
        if ($type != 2 && $type != 3) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized action.']);
            exit;
        }
        
        $dept_id = isset($_POST['dept_id']) ? intval($_POST['dept_id']) : 0;
        $dept_name = isset($_POST['dept_name']) ? trim($_POST['dept_name']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        
        if ($dept_id <= 0 || empty($dept_name)) {
            echo json_encode(['success' => false, 'message' => 'Invalid department data.']);
            exit;
        }
        
        // Get old department name to update users
        $oldNameQuery = $conn->prepare("SELECT dept_name FROM college_departments WHERE dept_id = ?");
        $oldNameQuery->bind_param("i", $dept_id);
        $oldNameQuery->execute();
        $oldNameResult = $oldNameQuery->get_result();
        $oldNameRow = $oldNameResult->fetch_assoc();
        $oldDeptName = $oldNameRow['dept_name'];
        $oldNameQuery->close();
        
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Update department
            $updateDeptStmt = $conn->prepare("UPDATE college_departments SET dept_name = ?, description = ? WHERE dept_id = ?");
            $updateDeptStmt->bind_param("ssi", $dept_name, $description, $dept_id);
            $updateDeptStmt->execute();
            $updateDeptStmt->close();
            
            // Update users with this department
            $updateUsersStmt = $conn->prepare("UPDATE nx_users SET college_department = ? WHERE college_department = ?");
            $updateUsersStmt->bind_param("ss", $dept_name, $oldDeptName);
            $updateUsersStmt->execute();
            $updateUsersStmt->close();
            
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Department updated successfully.']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to update department: ' . $e->getMessage()]);
        }
        exit;
    } elseif ($action === 'delete') {
        // Only admin users can delete departments
        if ($type != 2 && $type != 3) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized action.']);
            exit;
        }
        
        $dept_id = isset($_POST['dept_id']) ? intval($_POST['dept_id']) : 0;
        
        if ($dept_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid department id.']);
            exit;
        }
        
        // Get department name
        $deptQuery = $conn->prepare("SELECT dept_name FROM college_departments WHERE dept_id = ?");
        $deptQuery->bind_param("i", $dept_id);
        $deptQuery->execute();
        $deptResult = $deptQuery->get_result();
        $deptRow = $deptResult->fetch_assoc();
        $deptName = $deptRow['dept_name'];
        $deptQuery->close();
        
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Delete department
            $deleteDeptStmt = $conn->prepare("DELETE FROM college_departments WHERE dept_id = ?");
            $deleteDeptStmt->bind_param("i", $dept_id);
            $deleteDeptStmt->execute();
            $deleteDeptStmt->close();
            
            // Clear department from users
            $clearUsersStmt = $conn->prepare("UPDATE nx_users SET college_department = NULL WHERE college_department = ?");
            $clearUsersStmt->bind_param("s", $deptName);
            $clearUsersStmt->execute();
            $clearUsersStmt->close();
            
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Department deleted successfully.']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to delete department: ' . $e->getMessage()]);
        }
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        exit;
    }
}

// Get current user's department
$userDeptQuery = $conn->prepare("SELECT college_department FROM nx_users WHERE pID = ?");
$userDeptQuery->bind_param("i", $userId);
$userDeptQuery->execute();
$userDeptResult = $userDeptQuery->get_result();
$userDeptRow = $userDeptResult->fetch_assoc();
$userCurrentDept = $userDeptRow['college_department'];
$userDeptQuery->close();

// Get all departments
$departmentsQuery = "SELECT * FROM college_departments ORDER BY dept_id DESC";
$departmentsResult = $conn->query($departmentsQuery);
if ($departmentsResult === false) {
    die("Error executing query: " . $conn->error);
}

$departments = [];
if ($departmentsResult->num_rows > 0) {
    while ($row = $departmentsResult->fetch_assoc()) {
        $departments[] = $row;
    }
}

// Get department member counts
$memberCountsQuery = "SELECT college_department, COUNT(*) as member_count FROM nx_users WHERE college_department IS NOT NULL GROUP BY college_department";
$memberCountsResult = $conn->query($memberCountsQuery);

$memberCounts = [];
if ($memberCountsResult->num_rows > 0) {
    while ($row = $memberCountsResult->fetch_assoc()) {
        $memberCounts[$row['college_department']] = $row['member_count'];
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php include_once '../header_cdn.php'; ?>
  <title>College Departments</title>
</head>
<body class="bg-gray-100 h-screen overflow-hidden">
  <?php include '../header.php'; ?>
  <div class="flex h-screen">
    <?php include '../sidebar.php'; ?>
    <div class="container mx-auto px-4 py-8 overflow-y-auto mb-16">
      <h2 class="text-3xl font-bold text-gray-800 mb-6">College Departments</h2>
      
      <!-- User's Current Department -->
      <div class="mb-6 bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-semibold mb-2">Your Current Department</h3>
        <?php if ($userCurrentDept): ?>
          <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4">
            <p class="font-bold">You are currently a member of: <?php echo htmlspecialchars($userCurrentDept); ?></p>
          </div>
        <?php else: ?>
          <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4">
            <p class="font-bold">You are not currently a member of any department.</p>
            <p>Please join a department from the list below.</p>
          </div>
        <?php endif; ?>
      </div>
      
      <!-- Only admin users can create departments -->
      <?php if ($type == 2 || $type == 3): ?>
      <div class="mb-6">
        <button onclick="openCreateModal()" class="px-4 py-2 bg-blue-500 text-white rounded-lg font-semibold hover:bg-blue-600">
          Create New Department
        </button>
      </div>
      <?php endif; ?>
      
      <!-- Departments Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <?php foreach ($departments as $dept): ?>
          <div class="bg-white rounded-lg shadow-md p-6">
            <h4 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($dept['dept_name']); ?></h4>
            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($dept['description'] ?: 'No description available.'); ?></p>
            
            <div class="text-sm text-gray-500 mb-4">
              <p>Members: <?php echo isset($memberCounts[$dept['dept_name']]) ? $memberCounts[$dept['dept_name']] : 0; ?></p>
            </div>
            
            <div class="mt-4">
              <?php if ($userCurrentDept == $dept['dept_name']): ?>
                <button disabled class="w-full px-4 py-2 bg-green-500 text-white rounded-lg font-semibold opacity-75 cursor-not-allowed">
                  Current Department
                </button>
              <?php else: ?>
                <button onclick="joinDepartment(<?php echo $dept['dept_id']; ?>, '<?php echo addslashes(htmlspecialchars($dept['dept_name'])); ?>')" 
                        class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg font-semibold hover:bg-blue-600">
                  Join Department
                </button>
              <?php endif; ?>
              
              <?php if ($type == 2 || $type == 3): ?>
                <div class="flex mt-2">
                  <button onclick="openEditModal(<?php echo $dept['dept_id']; ?>, '<?php echo addslashes(htmlspecialchars($dept['dept_name'])); ?>', '<?php echo addslashes(htmlspecialchars($dept['description'])); ?>')" 
                          class="flex-1 px-2 py-1 bg-yellow-500 text-white rounded-l hover:bg-yellow-600">
                    Edit
                  </button>
                  <button onclick="deleteDepartment(<?php echo $dept['dept_id']; ?>)" 
                          class="flex-1 px-2 py-1 bg-red-500 text-white rounded-r hover:bg-red-600">
                    Delete
                  </button>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      
      <!-- Departments Table View -->
      <h3 class="text-2xl font-semibold text-gray-700 mb-4">Departments List</h3>
      <table class="min-w-full bg-white rounded-lg shadow-md">
        <thead>
          <tr>
            <th class="py-2 px-4 border-b">Department ID</th>
            <th class="py-2 px-4 border-b">Department Name</th>
            <th class="py-2 px-4 border-b">Description</th>
            <th class="py-2 px-4 border-b">Members</th>
            <th class="py-2 px-4 border-b">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($departments)): ?>
            <?php foreach ($departments as $dept): ?>
              <tr>
                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($dept['dept_id']); ?></td>
                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($dept['dept_name']); ?></td>
                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($dept['description']); ?></td>
                <td class="py-2 px-4 border-b">
                  <?php echo isset($memberCounts[$dept['dept_name']]) ? $memberCounts[$dept['dept_name']] : 0; ?>
                </td>
                <td class="py-2 px-4 border-b">
                  <?php if ($userCurrentDept == $dept['dept_name']): ?>
                    <button disabled class="px-2 py-1 bg-green-500 text-white rounded opacity-75 cursor-not-allowed">
                      Current Dept
                    </button>
                  <?php else: ?>
                    <button onclick="joinDepartment(<?php echo $dept['dept_id']; ?>, '<?php echo addslashes(htmlspecialchars($dept['dept_name'])); ?>')" class="px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                      Join
                    </button>
                  <?php endif; ?>
                  
                  <?php if ($type == 2 || $type == 3): ?>
                    <button onclick="openEditModal(<?php echo $dept['dept_id']; ?>, '<?php echo addslashes(htmlspecialchars($dept['dept_name'])); ?>', '<?php echo addslashes(htmlspecialchars($dept['description'])); ?>')" class="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 ml-2">
                      Edit
                    </button>
                    <button onclick="deleteDepartment(<?php echo $dept['dept_id']; ?>)" class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600 ml-2">
                      Delete
                    </button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="py-4 px-4 text-center">No departments found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  
  <!-- Create Department Modal -->
  <div id="createModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
      <h3 class="text-xl font-semibold mb-4">Create New Department</h3>
      <form id="createDeptForm">
        <div class="mb-4">
          <label for="dept_name" class="block text-gray-700">Department Name</label>
          <input type="text" id="dept_name" name="dept_name" class="w-full border px-3 py-2 rounded" required>
        </div>
        <div class="mb-4">
          <label for="description" class="block text-gray-700">Description</label>
          <textarea id="description" name="description" class="w-full border px-3 py-2 rounded"></textarea>
        </div>
        <div class="flex justify-end">
          <button type="button" onclick="closeCreateModal()" class="mr-2 px-4 py-2 bg-gray-300 rounded">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">Create</button>
        </div>
      </form>
    </div>
  </div>
  
  <!-- Edit Department Modal -->
  <div id="editModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
      <h3 class="text-xl font-semibold mb-4">Edit Department</h3>
      <form id="editDeptForm">
        <input type="hidden" id="edit_dept_id" name="dept_id">
        <div class="mb-4">
          <label for="edit_dept_name" class="block text-gray-700">Department Name</label>
          <input type="text" id="edit_dept_name" name="dept_name" class="w-full border px-3 py-2 rounded" required>
        </div>
        <div class="mb-4">
          <label for="edit_description" class="block text-gray-700">Description</label>
          <textarea id="edit_description" name="description" class="w-full border px-3 py-2 rounded"></textarea>
        </div>
        <div class="flex justify-end">
          <button type="button" onclick="closeEditModal()" class="mr-2 px-4 py-2 bg-gray-300 rounded">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
  
  <!-- JavaScript -->
  <script>
    function joinDepartment(deptId, deptName) {
      if (confirm("Are you sure you want to join the " + deptName + " department?")) {
        const formData = new FormData();
        formData.append("action", "join_department");
        formData.append("dept_id", deptId);
        
        fetch("", {
          method: "POST",
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          alert(data.message);
          if (data.success) {
            location.reload();
          }
        })
        .catch(error => console.error("Error:", error));
      }
    }
    
    function openCreateModal() {
      document.getElementById("createModal").classList.remove("hidden");
    }
    
    function closeCreateModal() {
      document.getElementById("createModal").classList.add("hidden");
    }
    
    document.getElementById("createDeptForm")?.addEventListener("submit", function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      formData.append("action", "create");
      
      fetch("", {
        method: "POST",
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        alert(data.message);
        if (data.success) {
          location.reload();
        }
      })
      .catch(error => console.error("Error:", error));
    });
    
    function openEditModal(deptId, deptName, description) {
      document.getElementById("edit_dept_id").value = deptId;
      document.getElementById("edit_dept_name").value = deptName;
      document.getElementById("edit_description").value = description;
      document.getElementById("editModal").classList.remove("hidden");
    }
    
    function closeEditModal() {
      document.getElementById("editModal").classList.add("hidden");
    }
    
    document.getElementById("editDeptForm")?.addEventListener("submit", function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      formData.append("action", "edit");
      
      fetch("", {
        method: "POST",
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        alert(data.message);
        if (data.success) {
          location.reload();
        }
      })
      .catch(error => console.error("Error:", error));
    });
    
    function deleteDepartment(deptId) {
      if (confirm("Are you sure you want to delete this department? This action cannot be undone.")) {
        const formData = new FormData();
        formData.append("action", "delete");
        formData.append("dept_id", deptId);
        
        fetch("", {
          method: "POST",
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          alert(data.message);
          if (data.success) {
            location.reload();
          }
        })
        .catch(error => console.error("Error:", error));
      }
    }
  </script>
</body>
</html>
