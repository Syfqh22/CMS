<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

include('db_connection.php');

$message = "";
$action = $_GET['action'] ?? '';
$assignment_id = $_GET['id'] ?? '';

if ($action === 'delete' && !empty($assignment_id)) {
    $stmt = $conn->prepare("DELETE FROM assignments WHERE id = ? AND teacher_id = ?");
    $stmt->bind_param("ii", $assignment_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        $message = "Assignment deleted successfully!";
    } else {
        $message = "Error deleting assignment.";
    }
}

$stmt = $conn->prepare("
    SELECT assignments.id, assignments.title, assignments.description, classes.class_name 
    FROM assignments
    JOIN classes ON assignments.class_id = classes.id
    WHERE classes.teacher_id = ?
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
    <title>Manage Assignments</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div style="margin-bottom: 20px;">
        <a href="teacher_dashboard.php">
            <button style="padding: 10px 20px; background-color: #374d5b; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Back to Dashboard
            </button>
        </a>
    </div>

    <h1>Manage Assignments</h1>
    <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>

    <a href="add_assignments.php">
        <button style="padding: 10px 20px; background-color: #374d5b; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Add Assignment
        </button>
    </a>

    <h2>Your Assignments</h2>
    <table border="1" style="width: 100%; text-align: left;">
        <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Class</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($assignment = $assignments->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                    <td><?php echo htmlspecialchars($assignment['description']); ?></td>
                    <td><?php echo htmlspecialchars($assignment['class_name']); ?></td>
                    <td>
                        <a href="assignments_t.php?action=delete&id=<?php echo $assignment['id']; ?>" onclick="return confirm('Are you sure you want to delete this assignment?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
