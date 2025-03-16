<?php
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Colleges</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
<div class="w-full md:w-[200px] bg-[#dbe3f3] text-gray-800 shadow-md p-4 hidden md:block hover:bg-[#c1d2e1]" id="sidebars">
    <ul class="mt-4">
        <li>
            <a href="../../pages/dashboard/dashboard.php" class="block py-2 px-4 hover:bg-[#df7c0b] hover:scale-105 transition-transform">
                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="../../pages/dashboard/user_profile.php?id=<?php echo htmlspecialchars($user['id']); ?>" class="block py-2 px-4 hover:bg-[#df7c0b] hover:scale-105 transition-transform">
                <i class="fas fa-user mr-2"></i> Profile
            </a>
        </li>
        <li>
            <a href="../../pages/dashboard/batch.php" class="block py-2 px-4 hover:bg-[#df7c0b] hover:scale-105 transition-transform">
                <i class="fas fa-layer-group mr-2"></i> Batch
            </a>
        </li>
        <li>
            <a href="../../pages/dashboard/job_list.php" class="block py-2 px-4 hover:bg-[#df7c0b] hover:scale-105 transition-transform">
                <i class="fas fa-briefcase mr-2"></i> Job List
            </a>
        </li>
        <li>
            <a href="../../pages/dashboard/alumni.php" class="block py-2 px-4 hover:bg-[#df7c0b] hover:scale-105 transition-transform">
                <i class="fas fa-users mr-2"></i> Alumni
            </a>
        </li>
        <li>
            <a href="../../pages/dashboard/colleges.php" class="block py-2 px-4 hover:bg-[#df7c0b] hover:scale-105 transition-transform">
            <i class="fas fa-university mr-2"></i> Colleges
            </a>
        </li>
        <li>
            <a href="../../pages/dashboard/settings.php" class="block py-2 px-4 hover:bg-[#df7c0b] hover:scale-105 transition-transform">
                <i class="fas fa-cog mr-2"></i> Settings
            </a>
        </li>
        <li>
            <a href="../pages/session_stop.php" class="sm:hidden block py-2 px-4 hover:bg-[#df7c0b] hover:scale-105 transition-transform">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
        </li>
    </ul>
</div>
<script>
function toggleSidebar() {
    const sidebars = document.getElementById('sidebars');
    if (sidebars) {
        sidebars.classList.toggle('hidden');
    } else {
        console.error('Sidebar not found.');
    }
}
</script>
</body>
</html>
