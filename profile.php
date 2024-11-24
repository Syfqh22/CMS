<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: index.php");
    exit;
}

include('db_connection.php');

$message = "";

$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($email)) {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssi", $username, $email, $hashed_password, $_SESSION['user_id']);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $username, $email, $_SESSION['user_id']);
        }

        if ($stmt->execute()) {
            $message = "Profile updated successfully!";
        } else {
            $message = "Error updating profile.";
        }
    } else {
        $message = "Username and email are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div style="margin-bottom: 20px;">
        <a href="student_dashboard.php">
            <button style="padding: 10px 20px; background-color: #374d5b; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Back to Dashboard
            </button>
        </a>
    </div>

    <h1>My Profile</h1>
    <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>

    <form action="profile.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        <br>
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        <br>
        <label for="password">New Password (leave blank to keep current):</label>
        <input type="password" name="password" id="password">
        <br>
        <button type="submit">Update Profile</button>
    </form>
</body>
</html>
