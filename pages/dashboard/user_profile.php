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
    $sql = "SELECT *
        FROM nx_users u 
        LEFT JOIN nx_employees e ON u.pID = e.pID 
        LEFT JOIN nx_user_batches ub ON u.pID = ub.pID 
        LEFT JOIN nx_batches b ON ub.batchID = b.batchID 
        WHERE u.pID = $userID";


    $result = mysqli_query($conn, $sql);


    if ($result && mysqli_num_rows($result) > 0) {
        $userDetails = mysqli_fetch_assoc($result);
        mysqli_free_result($result);

    } else {
        // Handle the case where no data is returned
        $userDetails = []; // Or set a message to indicate no data
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
$hidden = '';
// Check if the viewed user is already a friend
$isFriend = false;
$isPendingRequest = false;

if (!$isOwnProfile) {
    $hidden = 'style="display: none"';
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
function getDepartmentName($code) {
    $departments = [
        'cbea' => 'College of Business and Accountancy',
        'cas' => 'College of Science',
        'CEng' => 'College of Engineering',
        'Ced' => 'College of Education',
        'IA' => 'Institute of Architecture',
        'Ics' => 'Institute of Computer Studies',
        'ihk' => 'Institute of Human Kinetics'
    ];
    
    return isset($departments[$code]) ? $departments[$code] : $code;
}
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
            background-image: url('../../images/OIP.jpg');
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
                        <form id="profilePictureForm" enctype="multipart/form-data" method="post">
                            <img 
                                src="../../images/pfp/<?= htmlspecialchars($userDetails['profile_picture'] ?: '../../images/pfp/default.jpg') ?>" 
                                alt="Profile Picture" 
                                class="w-40 h-40 rounded-full border-4 border-white cursor-pointer" 
                                id="profileImagePreview" 
                                onclick="document.getElementById('profilePictureInput').click();"
                            >
                            <input 
                                type="file" 
                                id="profilePictureInput" 
                                name="profile_picture" 
                                accept="image/*" 
                                style="display: none;"
                            >
                        </form>


                            <div class="ml-4 text-white">
                                <h1 class="text-3xl font-bold"><?= htmlspecialchars($userDetails['fname'] . ' ' . $userDetails['lname']) ?></h1>
                                <p class="text-lg">@<?= htmlspecialchars($userDetails['username']) ?></p>
                            </div>
                            <?php if (!$isOwnProfile): ?>
                                <?php if ($isFriend): ?>
                                    <button id="cancelFriendBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-300">Remove Friend</button>
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
                            <!-- <button class="tab-button px-4 py-3 text-gray-600 hover:bg-gray-100" data-tab="photos">Photos</button> -->
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
                                <!-- <div class="bg-white shadow rounded-lg p-4 mb-4" <?=$hidden?>>
                                    <textarea class="w-full p-2 border rounded-lg" placeholder="What's on your mind?"></textarea>
                                    <button class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg">Post</button>
                                </div> -->

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

                                    <!-- Hide sensitive info if not own profile or not a friend -->
                                    <p><strong>Email:</strong> <?= $isOwnProfile || $isFriend ? htmlspecialchars($userDetails['email']) : '<span style="color: gray; font-style: italic;">Hidden</span>' ?></p>
                                    <p><strong>Date of Birth:</strong> <?= $isOwnProfile || $isFriend ? htmlspecialchars($userDetails['date_of_birth']) : '<span style="color: gray; font-style: italic;">Hidden</span>' ?></p>
                                    <p><strong>Phone Number:</strong> <?= $isOwnProfile || $isFriend ? htmlspecialchars($userDetails['phone_number']) : '<span style="color: gray; font-style: italic;">Hidden</span>' ?></p>
                                    <p><strong>Address:</strong> <?= $isOwnProfile || $isFriend ? htmlspecialchars($userDetails['address']) . ', ' . htmlspecialchars($userDetails['city']) . ', ' . htmlspecialchars($userDetails['state']) . ' ' . htmlspecialchars($userDetails['zip_code']) . ', ' . htmlspecialchars($userDetails['country']) : '<span style="color: gray; font-style: italic;">Hidden</span>' ?></p>
                                </div>

                                <div>
                                    <h3 class="text-lg font-semibold mb-2">Additional Information</h3>

                                    <p><strong>Bio:</strong> <?= !empty($userDetails['bio']) ? nl2br(htmlspecialchars($userDetails['bio'])) : 'No information available.' ?></p>
                                    <p><strong>Position:</strong> <?= !empty($userDetails['position']) ? htmlspecialchars($userDetails['position']) : 'No information available.' ?></p>
                                    <p><strong>College Graduate:</strong> <?= isset($userDetails['college_graduate']) ? ($userDetails['college_graduate'] == 1 ? 'Yes' : 'No') : 'No information available.' ?></p>
                                    <p><strong>Department:</strong> <?= !empty($userDetails['college_department']) ? htmlspecialchars(getDepartmentName($userDetails['college_department'])) : 'No information available.' ?></p>
                                    <p><strong>Graduation Date:</strong> <?= !empty($userDetails['graduation_date']) ? htmlspecialchars($userDetails['graduation_date']) : 'No information available.' ?></p>
                                    <p><strong>Batch:</strong> <?= !empty($userDetails['batch_name']) ? htmlspecialchars($userDetails['batch_name']) : 'No information available.' ?></p>
                                </div>
                            </div>
                        </div>
                    </div>



                    <!-- Friends Tab -->
                    <div id="friends" class="tab-content">
                        <div class="bg-white shadow rounded-lg p-4">
                            <h2 class="text-2xl font-semibold mb-4">Friends</h2>
                            <?php if (!empty($friends)): ?>
                                <ul class="md:grid md:grid-cols-3 md:gap-4">
                                    <?php foreach ($friends as $friend): ?>
                                        <li class="md:mb-0 mb-3 flex items-center bg-gray-100 p-2 rounded-lg" data-id="<?php echo htmlspecialchars($friend['pID']); ?>">
                                            <a href="user_profile.php?id=<?php echo htmlspecialchars($friend['pID']); ?>" class="flex items-center ">
                                                <img src="../../images/pfp/<?php echo htmlspecialchars($friend['profile_picture']) ?: '../../images/pfp/default.jpg'; ?>" alt="<?php echo htmlspecialchars($friend['username']); ?>" class="w-10 h-10 rounded-full mr-2">
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
                    <!-- <div id="photos" class="tab-content">
                        <div class="bg-white shadow rounded-lg p-4">
                            <h2 class="text-2xl font-semibold mb-4">Photos</h2>
                           
                            <p class="text-gray-600">No photos to display.</p>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
var userID = <?= $userID ?>;

$(document).ready(function () {
    $('#profilePictureInput').on('change', function (event) {
        const file = event.target.files[0];
        if (file) {
            const formData = new FormData();
            formData.append('profile_picture', file);

            $.ajax({
                url: '../dashboard/query/upload_profile_picture.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (data) {
                    if (data.success) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            $('#profileImagePreview').attr('src', e.target.result);
                        };
                        reader.readAsDataURL(file);
                        toastr.success('Profile picture updated successfully!');
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error('Failed to update profile picture. Please try again.');
                    }
                },
                error: function () {
                    toastr.error('An error occurred. Please try again.');
                }
            });
        }
    });

    $('#friends li').on('click', function () {
        const friendId = $(this).data('id');
        console.log('Friend ID:', friendId);
    });

    $('.tab-button').on('click', function () {
        const tabName = $(this).data('tab');
        $('.tab-button').removeClass('text-blue-600 border-b-2 border-blue-600');
        $('.tab-content').removeClass('active');
        $(this).addClass('text-blue-600 border-b-2 border-blue-600');
        $('#' + tabName).addClass('active');
    });

    $('#addFriendBtn').on('click', function () {
        $.ajax({
            url: '../dashboard/query/add_friend.php',
            type: 'POST',
            data: { friendID: userID },
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    toastr.success('Friend request sent!');
                    $('#addFriendBtn').hide();
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error('Failed to send friend request. Please try again.');
                }
            },
            error: function () {
                toastr.error('An error occurred. Please try again.');
            }
        });
    });

    $('#cancelFriendBtn').on('click', function () {
        $.ajax({
            url: '../dashboard/query/cancel_friend.php',
            type: 'POST',
            data: { friendID: userID },
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    toastr.success('Friendship canceled.');
                    $('#cancelFriendBtn').hide();
                    $('#addFriendBtn').show();
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error(data.message);
                }
            },
            error: function () {
                toastr.error('An error occurred. Please try again.');
            }
        });
    });

    $('#cancelRequestBtn').on('click', function () {
        $.ajax({
            url: '../dashboard/query/cancel_friendrequest.php',
            type: 'POST',
            data: { friendID: userID },
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    toastr.success('Friend request canceled.');
                    $('#cancelRequestBtn').hide();
                    $('#addFriendBtn').show();
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error(data.message);
                }
            },
            error: function () {
                toastr.error('An error occurred. Please try again.');
            }
        });
    });
});
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