<?php
include('db_connection.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$signup_error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? null;
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;
    $user_type = $_POST['user_type'] ?? null;

    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

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
} else {
    $signup_error = "Invalid request!";
}

if ($signup_error) {
    echo "<p class='error'>$signup_error</p>";
}
?>
