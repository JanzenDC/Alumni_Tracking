<?php 
require_once '../backend/db_connect.php'; 
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];

// Search users if the query is at least 2 characters
if (strlen($search) >= 2) {
    $searchParam = "%$search%";

    // Search in nx_users
    $userSql = "SELECT pID, username, fname, lname, email, profile_picture FROM nx_users 
                WHERE username LIKE '$searchParam' OR fname LIKE '$searchParam' OR lname LIKE '$searchParam' OR email LIKE '$searchParam'
                LIMIT 10";

    $userResult = mysqli_query($conn, $userSql);
    if ($userResult) {
        while ($row = mysqli_fetch_assoc($userResult)) {
            $results[] = [
                'type' => 'user',
                'data' => $row
            ];
        }
        mysqli_free_result($userResult);
    }

    // Search in nx_batches
    $batchSql = "SELECT batchID, batch_name, batch_date, cover_photo, description FROM nx_batches 
                 WHERE batch_name LIKE '$searchParam'
                 LIMIT 10";

    $batchResult = mysqli_query($conn, $batchSql);
    if ($batchResult) {
        while ($row = mysqli_fetch_assoc($batchResult)) {
            $results[] = [
                'type' => 'batch',
                'data' => $row
            ];
        }
        mysqli_free_result($batchResult);
    }
}

// Display results
if (empty($results)) {
    echo '<p class="p-4 text-gray-500">No results found.</p>';
} else {
    $displayedResults = 0;
    foreach ($results as $result) {
        if ($displayedResults >= 3) break; // Display only the first 3 results

        if ($result['type'] === 'user') {
            $user = $result['data'];
            $profilePic = $user['profile_picture'] ? htmlspecialchars($user['profile_picture']) : '../../images/pfp/default.jpg';
            echo '
            <a href="../../pages/dashboard/user_profile.php?id=' . $user['pID'] . '" class="block hover:bg-gray-100">
                <div class="flex items-center p-4 border-b">
                    <img src="../../images/pfp/' . $profilePic . '" alt="' . htmlspecialchars($user['username']) . '" class="w-10 h-10 rounded-full mr-3">
                    <div>
                        <p class="font-semibold">' . htmlspecialchars($user['username']) . '</p>
                        <p class="text-sm text-gray-600">' . htmlspecialchars($user['fname'] . ' ' . $user['lname']) . '</p>
                    </div>
                </div>
            </a>';
        } elseif ($result['type'] === 'batch') {
            $batch = $result['data'];
            $coverPhoto = $batch['cover_photo'] ? htmlspecialchars($batch['cover_photo']) : '../../images/batch/default.jpg';
            echo '
            <a href="../../pages/dashboard/batch_details.php?id=' . $batch['batchID'] . '" class="block hover:bg-gray-100">
                <div class="flex items-center p-4 border-b">
                    <img src="../../images/batch_group_images/' . $coverPhoto . '" alt="' . htmlspecialchars($batch['batch_name']) . '" class="w-10 h-10 rounded mr-3">
                    <div>
                        <p class="font-semibold">' . htmlspecialchars($batch['batch_name']) . '</p>
                        <p class="text-sm text-gray-600">' . htmlspecialchars($batch['description']) . '</p>
                    </div>
                </div>
            </a>';
        }
        $displayedResults++;
    }

    // If there are more than 3 results, display a "See More" link
    if (count($results) > 3) {
        $moreLink = (count(array_filter($results, fn($r) => $r['type'] === 'user')) > 0) 
            ? '../dashboard/user_profile.php'
            : '../dashboard/batch.php';
        
        echo '<a href="' . $moreLink . '" class="block p-4 text-blue-500 hover:underline">See More...</a>';
    }
}
?>
