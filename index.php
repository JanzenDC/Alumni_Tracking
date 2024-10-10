<?php
require_once 'backend/toaster_handler.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once 'includes/header_cdn.php'; ?>

    <title>Alumni Tracking</title>

    <style>
        .bg-image {
            background-image: url('./images/MIC_7301.jpg');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body>
    <div class="flex h-screen">
        <div class="w-full md:w-1/2 flex items-center justify-center ">

            <form class="bg-white p-8 w-[500px] " action="backend/login_con.php" method="POST" enctype="multipart/form-data">
                <h2 class="text-[100px] mb-6 text-center bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-500">
                <i class="fa-solid fa-graduation-cap"></i>
                </h2>
                <input type="text" placeholder="Username" class="block w-full mb-4 p-2 border rounded" name="username">
                <input type="password" placeholder="Password" class="block w-full mb-4 p-2 border rounded" name="password">
                <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded">Login</button>
                <div class=" text-center mt-10">
                    Don't have an account?<a class="text-blue-700 font-bold" href="signup.php"> Sign Up</a>
                </div>
            </form>
        </div>
        <div class="hidden md:block md:w-1/2 bg-image">
            <!-- Fallback text in case image doesn't load -->
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php
            if (!empty($toastrScript)) {
                echo $toastrScript;
            }
            ?>
        });
    </script>
</body>
</html>