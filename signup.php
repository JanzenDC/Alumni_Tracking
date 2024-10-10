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
        .name-input {
            flex: 1;
            margin-right: 10px;
        }
        .name-input:last-child {
            margin-right: 0; /* Remove margin from the last input */
        }
        input {
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

        <form id="registrationForm" class="bg-white p-8 w-[500px]" action="signup.php" method="POST" enctype="multipart/form-data">
            <h2 class="text-[60px] text-center bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-500">
                <i class="fa-solid fa-user-plus"></i>
            </h2>

            <div class="form-step" id="step-1">
                <div class="name-container">
                    <input type="text" name="fname" placeholder="First Name" class="name-input" required>
                    <input type="text" name="mname" placeholder="Middle Name" class="name-input">
                    <input type="text" name="lname" placeholder="Last Name" class="name-input" required>
                </div>
                <input type="date" name="date_of_birth" placeholder="Date of Birth" required>
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="text" name="username" placeholder="Username" required>
                <div class="buttons">
                    <button type="button" onclick="nextStep()">Next</button>
                </div>
            </div>

            <div class="form-step" id="step-2" style="display: none;">
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <input type="text" name="phone_number" placeholder="Phone Number">
                <div class="buttons">
                    <button type="button" onclick="prevStep()">Previous</button>
                    <button type="button" onclick="nextStep()">Next</button>
                </div>
            </div>

            <div class="form-step" id="step-3" style="display: none;">
                <div class="form-grid">
                    <div class="form-group">
                        <input type="text" name="address" placeholder="Address">
                    </div>
                    <div class="form-group">
                        <input type="text" name="city" placeholder="City">
                    </div>
                    <div class="form-group">
                        <input type="text" name="state" placeholder="State">
                    </div>
                    <div class="form-group">
                        <input type="text" name="zip_code" placeholder="Zip Code">
                    </div>
                    <div class="form-group">
                        <input type="text" name="country" placeholder="Country">
                    </div>
                    <div class="form-group">
                        <textarea name="bio" placeholder="Bio"></textarea>
                    </div>
                    <div class="form-group">
                        <input type="file" name="profile_picture" placeholder="Profile Picture">
                    </div>
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

    <?php include_once 'includes/footer_cdn.php';?>


    <!-- SCRIPT -->
    <script>
    let currentStep = 1;
    const steps = document.querySelectorAll('.form-step');

    function showStep(step) {
        steps.forEach((s, index) => {
            s.style.display = (index + 1 === step) ? 'block' : 'none';
        });
    }

    function nextStep() {
        if (currentStep < steps.length) {
            currentStep++;
            showStep(currentStep);
        }
    }

    function prevStep() {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
        }
    }

    // Show the first step initially
    showStep(currentStep);
</script>
</body>
</html>
