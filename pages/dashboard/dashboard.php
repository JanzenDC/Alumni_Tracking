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
$sql_events = "SELECT * FROM nx_events ORDER BY event_date DESC"; // Fetch events in descending order by date
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
        /* Define pastel colors */
        .bg-pastel-blue { background-color: #D6EAF8; }
        .bg-pastel-green { background-color: #D4EFDF; }
        .bg-pastel-yellow { background-color: #FCF3CF; }
        .bg-pastel-pink { background-color: #FADBD8; }
        .bg-pastel-purple { background-color: #E8DAEF; }

        /* Blinking Announcement Icon */
        @keyframes blink {
            50% { opacity: 0.4; }
        }
        .blinking {
            animation: blink 1s infinite alternate;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include '../header.php'; ?>
    <div class="flex h-screen">
        <?php include '../sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="flex-1 p-4 md:p-6 overflow-y-auto">
            <?= $holding ?>
            
            <h1 class="text-2xl font-bold mb-4">Welcome, <?php echo htmlspecialchars($user['fname']); ?>!</h1>
            
            <!-- Intro Section -->
            <div class="mt-4 bg-white shadow-lg rounded-lg p-5">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-info-circle text-blue-500"></i> Intro
                </h2>
                <p class="mt-2">
                    This page is exclusively made for all RTU Alumni members about the on-going activities/projects 
                    of the RTU Grand Alumni Association Incorporated
                </p>
                <p class="mt-2">
                    <strong>Page:</strong> Higher education
                </p>
                <p class="mt-2">
                    4th Floor Alumni Center, MultiPurpose Hall, Rizal Technological University
                </p>
                <p class="mt-2">
                    (02) 429 9170
                </p>
                <p class="mt-2">
                    <a href="mailto:rtugaa1@gmail.com" class="text-blue-600 hover:underline">rtugaa1@gmail.com</a>
                </p>
            </div>
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
                        <form id="create-event-form" action="create_event.php" method="POST" enctype="multipart/form-data" onsubmit="return showConfirmation(event)">
                            <input type="text" name="event_name" placeholder="Event Name" required class="border p-2 rounded w-full mb-2">
                            <textarea name="description" placeholder="Description" class="border p-2 rounded w-full mb-2"></textarea>
                            <input type="datetime-local" name="event_date" required class="border p-2 rounded w-full mb-2">
                            
                            <!-- Image upload input -->
                            <input type="file" name="event_image" accept="image/*" required class="border p-2 rounded w-full mb-2">
                            
                            <button type="submit" class="bg-blue-500 text-white p-2 rounded">Create Event</button>
                        </form>
                    </div>

                </div>
            <?php endif; ?>

            <!-- List of Events -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
            <div class="mt-4 bg-white shadow-lg rounded-lg p-5">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-calendar-alt text-blue-500"></i> List of Events
                </h2>
                <div class="mt-3 p-5">
                    <?php if (count($events) > 0): ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php 
                            $pastelColors = ['bg-pastel-blue', 'bg-pastel-green', 'bg-pastel-yellow', 'bg-pastel-pink', 'bg-pastel-purple'];
                            $colorIndex = 0;
                            ?>
                            <?php foreach ($events as $event): 
                                $date = new DateTime($event['event_date']);
                                $formattedDate = $date->format('l, F j, Y \a\t g:i A');
                                $currentColor = $pastelColors[$colorIndex % count($pastelColors)]; 
                                $colorIndex++;

                                $imagePath = '../../images/' . htmlspecialchars($event['image']);
                                $defaultImage = '../../images/default-image-icon-vector-missin.jpg'; // <- Make sure this image exists
                                $finalImage = (!empty($event['image']) && file_exists($imagePath)) ? $imagePath : $defaultImage;
                            ?>
                                <div class="p-4 rounded-lg shadow-md transform transition-transform duration-300 hover:scale-105 hover:shadow-xl <?php echo $currentColor; ?>">
                                    <div class="flex flex-col space-y-2">
                                    <img src="<?php echo $finalImage; ?>" alt="Event Image" class="w-full h-48 object-cover rounded-md shadow">

                                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                            <i class="fas fa-bullhorn text-green-500 blinking"></i> 
                                            <?php echo htmlspecialchars($event['event_name']); ?>
                                        </h3>
                                            <p class="text-sm text-gray-600">
                                            <i class="fas fa-clock text-gray-500"></i>
                                            <?php echo htmlspecialchars($formattedDate); ?>
                                        </p>
                                        <p class="text-gray-700"><?php echo htmlspecialchars($event['description']); ?></p>

                                        <?php if ($isAdmin): ?>
                                            <form action="../dashboard/query/delete_event.php" method="POST" onsubmit="return confirmDelete(event)">
                                                <input type="hidden" name="eventID" value="<?php echo htmlspecialchars($event['eventID']); ?>">
                                                <button type="submit" class="text-red-500 hover:text-red-700 transition self-start mt-2">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-4">No events found.</p>
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
            // NOTE: If you want to actually call the delete_event.php after confirmation,
            // you should do form.submit() here, or fetch() with AJAX. The snippet below 
            // is only showing a placeholder for success/error messages.

            if (result.isConfirmed) {
                // For actual deletion, do something like:
                form.submit();
                Swal.fire({
                    icon: 'success',
                    title: '<i class="fas fa-check-circle"></i> Success!',
                    text: 'Event deleted.',
                });
                setTimeout(() => location.reload(), 2000);
            } else {
                Swal.fire({
                    icon: 'info',
                    title: 'Cancelled',
                    text: 'Deletion canceled.',
                });
            }
        });

        return false; // Prevent form submission until confirmed
    }
    </script>
</body>
</html>
