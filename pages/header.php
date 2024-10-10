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
            <input type="text" id="search-input" placeholder="Search users..." class="md:w-96 w-full bg-gray-700 text-white rounded-full py-2 px-4 pl-10 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            <div id="search-results" class="absolute w-96 mt-[50px] bg-white rounded-md shadow-lg hidden z-50 max-h-96 overflow-y-auto" style="min-width: 16rem;"></div>
        </div>
        <div class="flex space-x-4 relative">
            <p id="username" class="text-gray-300 hover:text-white cursor-pointer"><?= htmlspecialchars($username) ?></p>
            <div id="dropdown" class="absolute right-0 hidden bg-white shadow-lg rounded-md mt-[40px] w-40">
                <a href="#" id="logout" class="block px-4 py-2 text-gray-800 hover:bg-gray-200">Logout</a>
            </div>
        </div>
    </div>
</nav>

<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');
    const usernameElement = document.getElementById('username');
    const dropdown = document.getElementById('dropdown');
    let searchTimer;

    // Show/hide dropdown on username click
    usernameElement.addEventListener('click', function() {
        dropdown.classList.toggle('hidden');
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!usernameElement.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
        if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
            searchResults.style.display = 'none';
        }
    });

    // Search functionality
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

    // Logout confirmation
    document.getElementById('logout').addEventListener('click', function(event) {
        event.preventDefault(); // Prevent default link behavior
        Swal.fire({
            title: 'Are you sure?',
            text: "You will be logged out!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, logout!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Directly clear the session and redirect
                fetch('../session_stop.php') // Make sure to create this file
                    .then(() => {
                        window.location.href = '../../index.php'; // Redirect to login page
                    });
            }
        });
    });
});
</script>
