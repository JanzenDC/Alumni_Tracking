<?php
require_once 'backend/toaster_handler.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once 'includes/header_cdn.php'; ?>

    <title>Sign Up</title>

    <style>
        .bg-image {
            background-image: url('./images/MIC_7301.jpg');
            background-size: cover;
            background-position: center;
        }
        .name-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 16px; /* Space between name fields and the next input */
        }
        .name-field {
            flex: 1;
            margin-right: 10px;
        }
        .name-field:last-child {
            margin-right: 0; /* Remove margin from the last input */
        }
        label {
            display: block;
            margin-bottom: 4px;
            font-weight: bold;
        }
        input, textarea {
            width: 100%; /* Ensure all inputs take full width */
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 16px; /* Consistent margin between inputs */
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #4F46E5; /* Tailwind's blue-500 */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px; /* Adjusts spacing between fields */
        }
        .form-group {
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="flex h-screen">
        <div class="w-full md:w-1/2 flex items-center justify-center">
            <form id="registrationForm" class="bg-white p-8 w-[500px]" action="backend/signup_con.php" method="POST" enctype="multipart/form-data">
                <h2 class="text-[60px] text-center bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-500">
                    <i class="fa-solid fa-user-plus"></i>
                </h2>

                <div class="form-step" id="step-1">
                    <div class="name-container">
                        <div class="name-field">
                            <label for="fname">First Name</label>
                            <input type="text" id="fname" name="fname">
                        </div>
                        <div class="name-field">
                            <label for="mname">Middle Name</label>
                            <input type="text" id="mname" name="mname">
                        </div>
                        <div class="name-field">
                            <label for="lname">Last Name</label>
                            <input type="text" id="lname" name="lname">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="date_of_birth">Date of Birth</label>
                        <input type="date" id="date_of_birth" name="date_of_birth">
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username">
                    </div>
                    <div class="buttons">
                        <button type="button" onclick="nextStep()">Next</button>
                    </div>
                </div>

                <div class="form-step" id="step-2" style="display: none;">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                    <div class="form-group">
                        <label for="phone_number">Phone Number</label>
                        <input type="text" id="phone_number" name="phone_number">
                    </div>
                    <div class="buttons">
                        <button type="button" class="mb-3" onclick="prevStep()">Previous</button>
                        <button type="button" onclick="nextStep()">Next</button>
                    </div>
                </div>

                <div class="form-step" id="step-3" style="display: none;">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address">
                        </div>
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city">
                        </div>
                        <div class="form-group">
                            <label for="state">State</label>
                            <input type="text" id="state" name="state">
                        </div>
                        <div class="form-group">
                            <label for="zip_code">Zip Code</label>
                            <input type="text" id="zip_code" name="zip_code">
                        </div>
                        <div class="form-group">
                            <label for="country">Country</label>
                            <input type="text" id="country" name="country">
                        </div>
                        <div class="form-group">
                            <label for="bio">Bio</label>
                            <textarea id="bio" name="bio" class="w-full"></textarea>
                        </div>
                        <!-- <div class="form-group">
                            <label for="profile_picture">Profile Picture</label>
                            <input type="file" id="profile_picture" name="profile_picture">
                        </div> -->
                    </div>
                    <div class="buttons">
                        <button type="button" class="mb-3" onclick="prevStep()">Previous</button>
                        <button type="submit">Sign Up</button>
                    </div>
                </div>

                <div class="text-center mt-10">
                    Already have an account?<a href="index.php" class="text-blue-700 font-bold"> Log In</a>
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
        $(document).ready(function() {
            let currentStep = 1;
            const steps = $('.form-step');

            function showStep(step) {
                steps.hide().eq(step - 1).show();
            }

            // Show the first step initially
            showStep(currentStep);

            // Functions to change steps
            window.nextStep = function() {
                if (currentStep < steps.length) {
                    currentStep++;
                    showStep(currentStep);
                }
            };

            window.prevStep = function() {
                if (currentStep > 1) {
                    currentStep--;
                    showStep(currentStep);
                }
            };
        });
    </script>
</body>
</html>
