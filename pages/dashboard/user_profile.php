<?php
session_start();
require_once '../../backend/db_connect.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit;
}

$user = $_SESSION['user'];
$userID = isset($_GET['id']) ? intval($_GET['id']) : 0;

$userDetails = null;
if ($userID > 0) {
        $sql = "SELECT username, fname, lname, email, profile_picture, date_of_birth, bio, phone_number, address, city, state, zip_code, country 
        FROM nx_users 
        WHERE pID = $userID";
    $result = mysqli_query($conn, $sql);


    if ($result && mysqli_num_rows($result) > 0) {
        $userDetails = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
    }
}


if (!$userDetails) {
    echo '<p class="text-red-500">User not found.</p>';
    exit;
}
$logs = [];
$sqlLogs = "SELECT action, target_type, target_id, timestamp, remark FROM nx_logs WHERE pID = $userID ORDER BY timestamp DESC LIMIT 3";
$resultLogs = mysqli_query($conn, $sqlLogs);

if ($resultLogs) {
    while ($log = mysqli_fetch_assoc($resultLogs)) {
        $logs[] = $log;
    }
    mysqli_free_result($resultLogs);
}

$friends = [];
$sqlFriends = "SELECT u.pID, u.username, u.fname, u.lname, u.profile_picture 
               FROM nx_friends f
               JOIN nx_users u ON (u.pID = f.userID2 OR u.pID = f.userID1)
               WHERE (f.userID1 = $userID OR f.userID2 = $userID) AND f.status = 1
               AND u.pID != $userID";

$resultFriends = mysqli_query($conn, $sqlFriends);

if ($resultFriends) {
    while ($friend = mysqli_fetch_assoc($resultFriends)) {
        $friends[] = $friend;
    }
    mysqli_free_result($resultFriends);
}

$isOwnProfile = ($userID == $user['id']);

// Check if the viewed user is already a friend
$isFriend = false;
$isPendingRequest = false;

if (!$isOwnProfile) {
    $sqlCheckFriend = "SELECT status FROM nx_friends 
                       WHERE (userID1 = {$user['id']} AND userID2 = $userID)
                       OR (userID1 = $userID AND userID2 = {$user['id']})";
    $resultCheckFriend = mysqli_query($conn, $sqlCheckFriend);
    
    if ($resultCheckFriend && mysqli_num_rows($resultCheckFriend) > 0) {
        $friendStatus = mysqli_fetch_assoc($resultCheckFriend)['status'];
        $isFriend = ($friendStatus == 1);
        $isPendingRequest = ($friendStatus == 0); // Check if status is 0 for pending requests
    }
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once '../header_cdn.php'; ?>
    <title><?= htmlspecialchars($userDetails['fname'] . ' ' . $userDetails['lname']) ?> | Profile</title>
    <style>
        .cover-photo {
            height: 350px;
            background-color: #f0f2f5;
            background-image: url('path_to_default_cover_photo.jpg');
            background-size: cover;
            background-position: center;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .main-content {
            height: calc(100vh - 64px); /* Adjust based on your header height */
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-gray-100 overflow-hidden">
    <?php include '../header.php'; ?>
    <div class="flex h-screen ">
        <?php include '../sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="flex-1 overflow-hidden">
            <div class="main-content">
                <!-- Cover Photo -->
                <div class="cover-photo relative">
                    <div class="absolute bottom-0 left-0 w-full p-4 bg-gradient-to-t from-black to-transparent">
                        <div class="flex items-end">
                            <img src="<?= htmlspecialchars($userDetails['profile_picture'] ?: '../../images/pfp/default.jpg') ?>" alt="Profile Picture" class="w-40 h-40 rounded-full border-4 border-white">
                            <div class="ml-4 text-white">
                                <h1 class="text-3xl font-bold"><?= htmlspecialchars($userDetails['fname'] . ' ' . $userDetails['lname']) ?></h1>
                                <p class="text-lg">@<?= htmlspecialchars($userDetails['username']) ?></p>
                            </div>
                            <?php if (!$isOwnProfile): ?>
                                <?php if ($isFriend): ?>
                                    <button id="cancelFriendBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-300">Cancel Friendship</button>
                                <?php elseif ($isPendingRequest): ?>
                                    <button id="cancelRequestBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-300">Cancel Request</button>
                                <?php else: ?>
                                    <button id="addFriendBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-300">Add Friend</button>
                                <?php endif; ?>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>

                <!-- Profile Tabs -->
                <div class="bg-white shadow sticky top-0 z-10">
                    <div class="max-w-5xl mx-auto">
                        <nav class="flex">
                            <button class="tab-button px-4 py-3 font-semibold text-blue-600 border-b-2 border-blue-600" data-tab="timeline">Timeline</button>
                            <button class="tab-button px-4 py-3 text-gray-600 hover:bg-gray-100" data-tab="about">About</button>
                            <button class="tab-button px-4 py-3 text-gray-600 hover:bg-gray-100" data-tab="friends">Friends</button>
                            <button class="tab-button px-4 py-3 text-gray-600 hover:bg-gray-100" data-tab="photos">Photos</button>
                        </nav>
                    </div>
                </div>

                <!-- Profile Content -->
                <div class="max-w-5xl mx-auto mt-4 p-4">
                    <!-- Timeline Tab -->
                    <div id="timeline" class="tab-content active">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Left Sidebar -->
                            <div class="md:col-span-1">
                                <div class="bg-white shadow rounded-lg p-4 mb-4">
                                    <h2 class="text-xl font-semibold mb-2">Intro</h2>
                                    <p class="text-sm text-gray-600 mb-2"><strong>Email:</strong> <?= htmlspecialchars($userDetails['email']) ?></p>
                                    <!-- Add more user details here -->
                                </div>
                            </div>

                            <!-- Main Content -->
                            <div class="md:col-span-2">
                                <!-- Status Update Box -->
                                <div class="bg-white shadow rounded-lg p-4 mb-4">
                                    <textarea class="w-full p-2 border rounded-lg" placeholder="What's on your mind?"></textarea>
                                    <button class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg">Post</button>
                                </div>

                                <!-- Timeline Posts -->
                                <div class="bg-white shadow rounded-lg p-4 mb-4">
                                    <h3 class="font-semibold mb-2">Recent Activity</h3>
                                    <?php if (!empty($logs)): ?>
                                        <?php foreach ($logs as $log): ?>
                                            <div class="mb-2">
                                                <p class="text-gray-800"><?php echo htmlspecialchars($log['action']); ?></p>
                                                <p class="text-gray-600"><?php echo htmlspecialchars($log['timestamp']); ?></p>
                                                <?php if ($log['remark']): ?>
                                                    <p class="text-gray-500 italic"><?php echo htmlspecialchars($log['remark']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <hr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-gray-600">No recent activity to show.</p>
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- About Tab -->
                    <div id="about" class="tab-content">
                        <div class="bg-white shadow rounded-lg p-4">
                            <h2 class="text-2xl font-semibold mb-4">About <?= htmlspecialchars($userDetails['fname']) ?></h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <h3 class="text-lg font-semibold mb-2">Basic Information</h3>
                                    <p><strong>Name:</strong> <?= htmlspecialchars($userDetails['fname'] . ' ' . $userDetails['lname']) ?></p>
                                    <p><strong>Username:</strong> <?= htmlspecialchars($userDetails['username']) ?></p>
                                    <p><strong>Email:</strong> <?= htmlspecialchars($userDetails['email']) ?></p>
                                    <p><strong>Date of Birth:</strong> <?= htmlspecialchars($userDetails['date_of_birth']) ?></p>
                                    <p><strong>Phone Number:</strong> <?= htmlspecialchars($userDetails['phone_number']) ?></p>
                                    <p><strong>Address:</strong> <?= htmlspecialchars($userDetails['address']) . ', ' . htmlspecialchars($userDetails['city']) . ', ' . htmlspecialchars($userDetails['state']) . ' ' . htmlspecialchars($userDetails['zip_code']) . ', ' . htmlspecialchars($userDetails['country']) ?></p>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold mb-2">Additional Information</h3>
                                    <?php if (!empty($userDetails['bio'])): ?>
                                        <p><strong>Bio:</strong> <?= nl2br(htmlspecialchars($userDetails['bio'])) ?></p>
                                    <?php else: ?>
                                        <p><strong>Bio:</strong> No information available.</p>
                                    <?php endif; ?>

                                    <!-- Additional sections can be added here -->
                                    <?php if (!empty($userDetails['work'])): ?>
                                        <p><strong>Work:</strong> <?= htmlspecialchars($userDetails['work']) ?></p>
                                    <?php else: ?>
                                        <p><strong>Work:</strong> No information available.</p>
                                    <?php endif; ?>

                                    <?php if (!empty($userDetails['education'])): ?>
                                        <p><strong>Education:</strong> <?= htmlspecialchars($userDetails['education']) ?></p>
                                    <?php else: ?>
                                        <p><strong>Education:</strong> No information available.</p>
                                    <?php endif; ?>

                                    <?php if (!empty($userDetails['hobbies'])): ?>
                                        <p><strong>Hobbies:</strong> <?= htmlspecialchars($userDetails['hobbies']) ?></p>
                                    <?php else: ?>
                                        <p><strong>Hobbies:</strong> No information available.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>



                    <!-- Friends Tab -->
                    <div id="friends" class="tab-content">
                        <div class="bg-white shadow rounded-lg p-4">
                            <h2 class="text-2xl font-semibold mb-4">Friends</h2>
                            <?php if (!empty($friends)): ?>
                                <ul class="grid grid-cols-3 gap-4">
                                    <?php foreach ($friends as $friend): ?>
                                        <li class="flex items-center bg-gray-100 p-2 rounded-lg" data-id="<?php echo htmlspecialchars($friend['pID']); ?>">
                                            <a href="user_profile.php?id=<?php echo htmlspecialchars($friend['pID']); ?>" class="flex items-center">
                                                <img src="<?php echo htmlspecialchars($friend['profile_picture']) ?: '../../images/pfp/default.jpg'; ?>" alt="<?php echo htmlspecialchars($friend['username']); ?>" class="w-10 h-10 rounded-full mr-2">
                                                <div>
                                                    <p class="font-semibold"><?php echo htmlspecialchars($friend['fname'] . ' ' . $friend['lname']); ?></p>
                                                    <p class="text-gray-600">@<?php echo htmlspecialchars($friend['username']); ?></p>
                                                </div>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>

                            <?php else: ?>
                                <p class="text-gray-600">No friends to display.</p>
                            <?php endif; ?>
                        </div>
                    </div>


                    <!-- Photos Tab -->
                    <div id="photos" class="tab-content">
                        <div class="bg-white shadow rounded-lg p-4">
                            <h2 class="text-2xl font-semibold mb-4">Photos</h2>
                            <!-- Add a grid of photos here -->
                            <p class="text-gray-600">No photos to display.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const addFriendBtn = document.getElementById('addFriendBtn');
        const cancelFriendBtn = document.getElementById('cancelFriendBtn');
        const cancelRequestBtn = document.getElementById('cancelRequestBtn');
        document.querySelectorAll('#friends li').forEach(item => {
            item.addEventListener('click', function() {
                const friendId = this.getAttribute('data-id');
                console.log('Friend ID:', friendId);
                // You can now use friendId for further actions, like redirecting to their profile page
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const tabName = button.getAttribute('data-tab');
                    
                    tabButtons.forEach(btn => btn.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    button.classList.add('text-blue-600', 'border-b-2', 'border-blue-600');
                    document.getElementById(tabName).classList.add('active');
                });
            });
        });
        if (addFriendBtn) {
            document.addEventListener('DOMContentLoaded', function() {
                const addFriendBtn = document.getElementById('addFriendBtn');
                if (addFriendBtn) {
                    addFriendBtn.addEventListener('click', function() {
                        // Send AJAX request to add friend
                        fetch('../dashboard/query/add_friend.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'friendID=<?= $userID ?>'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                toastr.success('Friend request sent!');
                                addFriendBtn.style.display = 'none';
                            } else {
                                toastr.error('Failed to send friend request. Please try again.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            toastr.error('An error occurred. Please try again.');
                        });
                    });
                }
            });
        }



    // Cancel Friendship button logic
    if (cancelFriendBtn) {
        cancelFriendBtn.addEventListener('click', function() {
            fetch('../dashboard/query/cancel_friend.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'friendID=<?= $userID ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success('Friendship canceled.');
                    cancelFriendBtn.style.display = 'none';
                    addFriendBtn.style.display = 'block'; // Show add friend button if needed
                } else {
                    toastr.error(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('An error occurred. Please try again.');
            });
        });
    }

    // Cancel Request button logic
    if (cancelRequestBtn) {
        cancelRequestBtn.addEventListener('click', function() {
            fetch('../dashboard/query/cancel_friend.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'friendID=<?= $userID ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success('Friend request canceled.');
                    cancelRequestBtn.style.display = 'none'; // Hide cancel request button
                    addFriendBtn.style.display = 'block'; // Show add friend button if needed
                } else {
                    toastr.error(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('An error occurred. Please try again.');
            });
        });
    }

    </script>

    <?php if (isset($_SESSION['toastr_message'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
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