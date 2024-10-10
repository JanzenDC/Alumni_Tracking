<?php

require_once '../backend/db_connect.php'; 
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];

if (strlen($search) >= 2) {
    $searchParam = "%$search%";
    $sql = "SELECT pID, username, fname, lname, email, profile_picture FROM nx_users 
            WHERE username LIKE '$searchParam' OR fname LIKE '$searchParam' OR lname LIKE '$searchParam' OR email LIKE '$searchParam'
            LIMIT 10";
    
    $result = mysqli_query($conn, $sql);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $results[] = $row;
        }
        mysqli_free_result($result);
    }
}

if (empty($results)) {
    echo '<p class="p-4 text-gray-500">No results found.</p>';
} else {
    foreach ($results as $user) {
        $profilePic = $user['profile_picture'] ? htmlspecialchars($user['profile_picture']) : '../../images/pfp/default.jpg';
        echo '
        <a href="../../pages/dashboard/user_profile.php?id=' . $user['pID'] . '" class="block hover:bg-gray-100">
            <div class="flex items-center p-4 border-b">
                <img src="' . $profilePic . '" alt="' . htmlspecialchars($user['username']) . '" class="w-10 h-10 rounded-full mr-3">
                <div>
                    <p class="font-semibold">' . htmlspecialchars($user['username']) . '</p>
                    <p class="text-sm text-gray-600">' . htmlspecialchars($user['fname'] . ' ' . $user['lname']) . '</p>
                </div>
            </div>
        </a>';
    }
}
?>