<?php
include('db_connection.php');
session_start(); 

ini_set('display_errors', 1);
error_reporting(E_ALL);

$login_error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? null;
    $password = $_POST['password'] ?? null;

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type']; 

                if ($user['user_type'] === 'teacher') {
                    header("Location: teacher_dashboard.php");
                } elseif ($user['user_type'] === 'student') {
                    header("Location: student_dashboard.php");
                }
                exit;
            } else {
                $login_error = "Invalid password.";
            }
        } else {
            $login_error = "No account found with that username.";
        }
    } else {
        $login_error = "Both fields are required!";
    }
}

if ($login_error) {
    echo "<p class='error'>$login_error</p>";
}
?>
