<?php
session_start();
require_once '../../backend/db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php'); // Redirect to login page if not logged in
    exit;
}

$user = $_SESSION['user'];
$userId = $user['id']; // Assuming 'id' is the user ID in the session
$isAdmin = ($user['user_type'] > '2'); // Check if the user type is greater than 2

// Fetch employee details
$sql = "SELECT * FROM nx_employees WHERE pID = $userId";
$result = $conn->query($sql);
$holding = "";
if ($result->num_rows === 0) {
    $holding = '
    <div class="mb-3 p-2 w-full flex justify-between items-center bg-red-300 text-red-700 rounded">
        <p>You have not set your employee details. Click <a href="../dashboard/settings.php" class="font-bold cursor-pointer">here</a> to fill up</p>
        <i class="fa-solid fa-x cursor-pointer" onclick="this.parentElement.style.display=\'none\'"></i>
    </div>';
}

// Fetch events
$sql_events = "SELECT * FROM nx_events ORDER BY event_date DESC"; // Fetch events in ascending order by date
$events_result = $conn->query($sql_events);
$events = [];

if ($events_result->num_rows > 0) {
    while ($row = $events_result->fetch_assoc()) {
        $events[] = $row; // Store each event in the array
    }
}

$result->close();
$events_result->close();
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
    </style>
</head>
<body class="bg-gray-100">
    <?php include '../header.php'; ?>
    <div class="flex h-screen">
        <?php include '../sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="flex-1 p-4 md:p-6  overflow-y-auto mb-16">
            <?= $holding ?>
            <h1 class="text-2xl font-bold mb-4">Welcome, <?php echo htmlspecialchars($user['fname']); ?>!</h1>
            <p class="mt-2">Here is your dashboard content.</p>

            <!-- Create Event (Admin Only) -->
            <?php if ($isAdmin): ?>
                <div class="mt-4 bg-white shadow rounded-lg p-4">
                    <div class="flex justify-between">
                        <h2 class="text-lg font-semibold">Create New Event:</h2>
                        <span id="toggle-icon" class="toggle-button" onclick="toggleCreateEvent()">
                            <i class="fas fa-plus"></i> <!-- Plus icon -->
                        </span>
                    </div>
                    <div id="create-event-content" class="events-content mt-2 hidden"> <!-- Hidden by default -->
                        <form id="create-event-form" action="create_event.php" method="POST" onsubmit="return showConfirmation(event)">
                            <input type="text" name="event_name" placeholder="Event Name" required class="border p-2 rounded w-full mb-2">
                            <textarea name="description" placeholder="Description" class="border p-2 rounded w-full mb-2"></textarea>
                            <input type="datetime-local" name="event_date" required class="border p-2 rounded w-full mb-2">
                            <button type="submit" class="bg-blue-500 text-white p-2 rounded">Create Event</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- List of Events -->
            <div class="mt-4 bg-white shadow rounded-lg p-4">
                <h2 class="text-lg font-semibold flex justify-between items-center">
                    List of Events:
                </h2>
                <div id="" class="mt-2">
                    <?php if (count($events) > 0): ?>
                        <ul>
                            <?php foreach ($events as $event): 
                                $date = new DateTime($event['event_date']);
                                $formattedDate = $date->format('l, F j, Y \a\t g:i A'); // Example: "Friday, October 12, 2024 at 3:30 PM"
                                ?>
                                <li class="border-b py-2 drop-shadow-sm">
                                    
                                    <strong><?php echo htmlspecialchars($event['event_name']); ?></strong><br>
                                    <em><?php echo htmlspecialchars($formattedDate); ?></em><br>
                                    <p><?php echo htmlspecialchars($event['description']); ?></p>
                                    <?php if ($isAdmin): ?>
                                        <form action="../dashboard/query/delete_event.php" method="POST" class="inline" onsubmit="return confirmDelete(event)">
                                            <input type="hidden" name="eventID" value="<?php echo htmlspecialchars($event['eventID']); ?>">
                                            <button type="submit" class="text-red-500 hover:underline">Delete</button>
                                        </form>

                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No events found.</p>
                    <?php endif; ?>
                </div>
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
<!-- SweetAlert CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
<!-- SweetAlert JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

    <script>
    function toggleCreateEvent() {
        const createEventContent = document.getElementById('create-event-content');
        const toggleIcon = document.getElementById('toggle-icon');
        
        if (createEventContent.classList.contains('hidden')) {
            createEventContent.classList.remove('hidden');
            toggleIcon.innerHTML = '<i class="fas fa-minus"></i>'; // Change to minus icon
        } else {
            createEventContent.classList.add('hidden');
            toggleIcon.innerHTML = '<i class="fas fa-plus"></i>'; // Change to plus icon
        }
    }


    function showConfirmation(event) {
        event.preventDefault(); // Prevent the default form submission

        const form = document.getElementById('create-event-form');

        Swal.fire({
            title: '<i class="fas fa-exclamation-triangle"></i> Are you sure?',
            text: "Do you want to create this event?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, create it!',
            cancelButtonText: 'Cancel',
            customClass: {
                title: 'font-bold',
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit the form using AJAX
                const formData = new FormData(form);
                fetch('../dashboard/query/create_event.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '<i class="fas fa-check-circle"></i> Success!',
                            text: data.message,
                        });
                        form.reset(); // Clear the form
                        setTimeout(() => location.reload(), 2000);

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '<i class="fas fa-times-circle"></i> Error!',
                            text: data.message,
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: '<i class="fas fa-times-circle"></i> Error!',
                        text: "There was an error processing your request.",
                    });
                });
            } else {
                Swal.fire({
                    icon: 'info',
                    title: 'Cancelled',
                    text: 'Event creation canceled.',
                });
            }
        });
    }

    function confirmDelete(event) {
        event.preventDefault(); // Prevent the default form submission

        const form = event.target; // Get the form element

        Swal.fire({
            title: '<i class="fas fa-trash-alt"></i> Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: '<i class="fas fa-check-circle"></i> Success!',
                    text: data.message,
                });
                form.reset(); // Clear the form
                setTimeout(() => location.reload(), 2000);
            }else {
                Swal.fire({
                    icon: 'error',
                    title: '<i class="fas fa-times-circle"></i> Error!',
                    text: data.message,
                });
            }
        });

        return false; // Prevent form submission until confirmed
    }

    </script>
</body>
</html>
