<?php
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}
?>

<div class="w-full md:w-[200px] bg-white shadow-md p-4">
    <ul class="mt-4">
        <li><a href="../dashboard.php" class="block py-2 px-4 hover:bg-gray-200">Dashboard</a></li>
        <li><a href="../profile.php" class="block py-2 px-4 hover:bg-gray-200">Profile</a></li>
        <li><a href="../settings.php" class="block py-2 px-4 hover:bg-gray-200">Settings</a></li>
        <li><a href="../logout.php" class="block py-2 px-4 hover:bg-gray-200">Logout</a></li>
    </ul>
</div>
