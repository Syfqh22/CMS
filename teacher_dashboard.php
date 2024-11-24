<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link rel="stylesheet" href="dashboard.css"> 
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>

    <nav class="shelf">
        <a class="book home-page" href="class.php">Manage Class</a>
        <a class="book about-us" href="student.php">Students</a>
        <a class="book contact" href="reports.php">Reports</a>
        <a class="book faq" href="bulletin_t.php">Bulletin</a>
        <a class="book assignments" href="assignments_t.php">Assignments</a>
  
        <a class="book not-found" href="logout.php"></a>
 
        <span class="door left"></span>
        <span class="door right"></span>
    </nav>
</body>
</html>
