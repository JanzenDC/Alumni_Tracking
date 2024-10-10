<?php

if (!isset($_SESSION['user'])) {
    header('Location: ../index.php'); // Redirect to login page if not logged in
    exit;
}
$username = $_SESSION["user"]["username"];
?>

<nav class="bg-gray-800 p-4">
    <div class="container mx-auto flex justify-between items-center">
        <a href="../pages/dashboard/dashboard.php" class="text-white text-lg font-bold">Dashboard</a>
        <div class="flex space-x-4">
            <p class="text-gray-300 hover:text-white"><?= $username ?></p>
        </div>
    </div>
</nav>
