<?php
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php'); // Redirect to login page if not logged in
    exit;
}
$username = $_SESSION["user"]["username"];
?>

<nav class="bg-gray-800 p-4">
    <div class="container mx-auto flex justify-between items-center">
        <div class="text-white text-lg font-bold flex items-center">
            <i class="fas fa-bars mr-2"></i>
            <a href="../../pages/dashboard/dashboard.php" class="hover:text-gray-300">Dashboard</a>
        </div>
        <div class="relative flex justify-center">
            <input type="text" id="search-input" placeholder="Search users..." class="w-96 bg-gray-700 text-white rounded-full py-2 px-4 pl-10 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            <div id="search-results" class="absolute w-96 mt-[50px] bg-white rounded-md shadow-lg hidden z-50 max-h-96 overflow-y-auto" style="min-width: 16rem;"></div>

        </div>
        <div class="flex space-x-4 ">
            <p class="text-gray-300 hover:text-white"><?= htmlspecialchars($username) ?></p>
        </div>
    </div>
</nav>

</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');
    let searchTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimer);
        const query = this.value;
        
        if (query.length >= 2) {
            searchTimer = setTimeout(function() {
                fetch(`../../pages/search_users_ajax.php?q=${encodeURIComponent(query)}`)
                    .then(response => response.text())
                    .then(data => {
                        searchResults.innerHTML = data;
                        searchResults.style.display = 'block';
                    });
            }, 300);
        } else {
            searchResults.style.display = 'none';
        }
    });

    document.addEventListener('click', function(event) {
        if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
            searchResults.style.display = 'none';
        }
    });
});
</script>