<?php
include('db_connection.php');

$signup_error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];

    if (!empty($username) && !empty($email) && !empty($password) && !empty($user_type)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $signup_error = "Username or email already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (username, email, password, user_type, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $user_type);

            if ($stmt->execute()) {
                header("Location: index.php");
                exit;
            } else {
                $signup_error = "Failed to register. Please try again.";
            }
        }
    } else {
        $signup_error = "All fields are required!";
    }
} 

if ($signup_error) {
    echo "<p class='error'>$signup_error</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sign Up</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="form-container" id="form-container">
        <!-- Login Form -->
        <div class="form-box" id="login-box">
            <h2>Login</h2>
            <form action="login.php" method="POST">
                <div class="input-group">
                    <label for="login-name">Name</label>
                    <input type="text" id="login-name" name="username" required>
                </div>
                <div class="input-group">
                    <label for="login-password">Password</label>
                    <input type="password" id="login-password" name="password" required>
                </div>
                <button type="submit">Login</button>
                <p>
                    Don't have an account? 
                    <span class="toggle-form" id="to-signup">Sign Up</span>
                </p>
            </form>
        </div>

        <!-- Sign-Up Form -->
        <div class="form-box" id="signup-box">
            <h2>Sign Up</h2>
            <form action="signup.php" method="POST" id="signup-form">
                <div class="input-group">
                    <label for="signup-name">Name</label>
                    <input type="text" id="signup-name" name="username" required>
                </div>
                <div class="input-group">
                    <label for="signup-email">Email</label>
                    <input type="email" id="signup-email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="signup-password">Password</label>
                    <input type="password" id="signup-password" name="password" required>
                </div>
                <div class="input-group user-type">
                    <p>I am a</p>
                    <div class="user-grid" id="user-grid">
                        <div class="user-icon" data-role="teacher">
                            <img src="images/teacher-icon.png" alt="Teacher Icon">
                            <p>Teacher</p>
                        </div>
                        <div class="user-icon" data-role="student">
                            <img src="images/student-icon.png" alt="Student Icon">
                            <p>Student</p>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="user-type" name="user_type" required>
                <button type="submit">Sign Up</button>
                <p>
                    Already have an account? 
                    <span class="toggle-form" id="to-login">Login</span>
                </p>
            </form>
        </div>
    </div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
    const formContainer = document.querySelector(".form-container");

    const showSignup = document.getElementById("show-signup");
    const showLogin = document.getElementById("show-login");

    showSignup.addEventListener("click", () => {
        formContainer.classList.add("flipped");
    });

    showLogin.addEventListener("click", () => {
        formContainer.classList.remove("flipped");
      });
    });

    const formContainer = document.getElementById('form-container');
        const toSignup = document.getElementById('to-signup');
        const toLogin = document.getElementById('to-login');

        toSignup.addEventListener('click', () => {
            formContainer.classList.add('flipped');
        });

        toLogin.addEventListener('click', () => {
            formContainer.classList.remove('flipped');
        });

            document.addEventListener("DOMContentLoaded", () => {
        const userIcons = document.querySelectorAll(".user-icon");
        const userTypeInput = document.getElementById("user-type");

        userIcons.forEach(icon => {
            icon.addEventListener("click", () => {
                userIcons.forEach(icon => icon.classList.remove("selected"));

                icon.classList.add("selected");

                userTypeInput.value = icon.getAttribute("data-role");
            });
        });

        const signupForm = document.getElementById("signup-form");
        signupForm.addEventListener("submit", (e) => {
            if (!userTypeInput.value) {
                e.preventDefault();
                alert("Please select a user type before signing up.");
            }
        });
    });

</script>

</body>
</html>