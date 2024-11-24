<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

include('db_connection.php');

$message = "";

$class_stmt = $conn->prepare("SELECT id, class_name FROM classes WHERE teacher_id = ?");
$class_stmt->bind_param("i", $_SESSION['user_id']);
$class_stmt->execute();
$classes = $class_stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $class_id = $_POST['class_id'] ?? '';

    if (!empty($title) && !empty($description) && !empty($class_id)) {
        $stmt = $conn->prepare("INSERT INTO assignments (title, description, class_id, teacher_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $title, $description, $class_id, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $message = "Assignment added successfully!";
        } else {
            $message = "Error adding assignment.";
        }
    } else {
        $message = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Assignment</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div style="margin-bottom: 20px;">
        <a href="assignments_t.php">
            <button style="padding: 10px 20px; background-color: #374d5b; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Back to Assignments
            </button>
        </a>
    </div>

    <h1>Add Assignment</h1>
    <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>

    <form action="add_assignments.php" method="POST">
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" required>
        <br>
        <label for="description">Description:</label>
        <textarea name="description" id="description" rows="5" required></textarea>
        <br>
        <label for="class_id">Class:</label>
        <select name="class_id" id="class_id" required>
            <option value="">Select a Class</option>
            <?php while ($class = $classes->fetch_assoc()): ?>
                <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['class_name']); ?></option>
            <?php endwhile; ?>
        </select>
        <br>
        <button type="submit">Add Assignment</button>
    </form>
</body>
</html>
