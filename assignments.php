<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: index.php");
    exit;
}

include('db_connection.php');

$stmt = $conn->prepare("
    SELECT assignments.title, assignments.description, classes.class_name, 
           grades.grade
    FROM assignments
    JOIN classes ON assignments.class_id = classes.id
    JOIN student_classes sc ON sc.class_id = classes.id
    LEFT JOIN grades ON grades.assignment_id = assignments.id AND grades.student_id = sc.student_id
    WHERE sc.student_id = ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$assignments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments</title>
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
    <h1>Your Assignments</h1>

    <?php if ($assignments->num_rows > 0): ?>
        <table border="1" style="width: 100%; text-align: left;">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Class</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($assignment = $assignments->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                        <td><?php echo htmlspecialchars($assignment['description']); ?></td>
                        <td><?php echo htmlspecialchars($assignment['class_name']); ?></td>
                        <td>
                            <?php 
                            if ($assignment['grade'] !== null) {
                                echo htmlspecialchars($assignment['grade']);
                            } else {
                                echo "Not Graded";
                            }
                            ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No assignments found for your classes.</p>
    <?php endif; ?>
</body>
</html>
