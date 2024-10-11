<?php
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}
?>

<div class="w-full md:w-[200px] bg-white shadow-md p-4 hidden md:block">
    <ul class="mt-4">
        <li><a href="../../pages/dashboard/dashboard.php" class="block py-2 px-4 hover:bg-gray-200">Dashboard</a></li>
        <li><a href="../../pages/dashboard/user_profile.php?id=<?php echo htmlspecialchars($user['id']); ?>" class="block py-2 px-4 hover:bg-gray-200">Profile</a></li>
        <li><a href="../../pages/dashboard/batch.php" class="block py-2 px-4 hover:bg-gray-200">Batch</a></li>
        <li><a href="../../pages/dashboard/job_list.php" class="block py-2 px-4 hover:bg-gray-200">Job List</a></li>
        <li><a href="../alumni.php" class="block py-2 px-4 hover:bg-gray-200">Alumni</a></li>
        <li><a href="../settings.php" class="block py-2 px-4 hover:bg-gray-200">Settings</a></li>
        <li><a href="../pages/session_stop.php" class="sm:hidden block py-2 px-4 hover:bg-gray-200">Logout</a></li>
    </ul>
</div>
