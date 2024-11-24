<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: index.php");
    exit;
}

include('db_connection.php');

$stmt = $conn->prepare("
    SELECT 
        classes.class_name, 
        classes.schedule, 
        users.username AS teacher_name
    FROM student_classes sc
    JOIN classes ON sc.class_id = classes.id
    JOIN users ON classes.teacher_id = users.id
    WHERE sc.student_id = ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$classes = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Classes</title>
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

    <h1>My Classes</h1>
    <?php if ($classes->num_rows > 0): ?>
        <table border="1" style="width: 100%; text-align: left;">
            <thead>
                <tr>
                    <th>Class Name</th>
                    <th>Schedule</th>
                    <th>Teacher</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($class = $classes->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                        <td><?php echo htmlspecialchars($class['schedule']); ?></td>
                        <td><?php echo htmlspecialchars($class['teacher_name']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color: red;">You are not enrolled in any classes yet.</p>
    <?php endif; ?>
</body>
</html>
