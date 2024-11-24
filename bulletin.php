<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: index.php");
    exit;
}

include('db_connection.php');

$stmt = $conn->prepare("
    SELECT bulletins.id, bulletins.title, bulletins.content, users.username AS teacher_name, bulletins.created_at
    FROM bulletins
    JOIN users ON bulletins.teacher_id = users.id
    ORDER BY bulletins.created_at DESC
");
$stmt->execute();
$bulletins = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletins</title>
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

    <h1>Bulletins</h1>

    <?php if ($bulletins->num_rows > 0): ?>
        <table border="1" style="width: 100%; text-align: left;">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Content</th>
                    <th>Teacher</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($bulletin = $bulletins->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($bulletin['title']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($bulletin['content'])); ?></td>
                        <td><?php echo htmlspecialchars($bulletin['teacher_name']); ?></td>
                        <td><?php echo date('F j, Y, g:i a', strtotime($bulletin['created_at'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color: red;">No bulletins available.</p>
    <?php endif; ?>
</body>
</html>
