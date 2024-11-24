<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

include('db_connection.php');

$message = "";
$action = $_GET['action'] ?? '';
$class_id = $_GET['id'] ?? '';
$search_results = [];
$search_query = $_GET['search'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_name = $_POST['class_name'] ?? '';
    $schedule = $_POST['schedule'] ?? '';

    if ($action === 'add' && !empty($class_name) && !empty($schedule)) {
        $stmt = $conn->prepare("INSERT INTO classes (class_name, schedule, teacher_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $class_name, $schedule, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $message = "Class added successfully!";
        } else {
            $message = "Error adding class.";
        }
    } elseif ($action === 'update' && !empty($class_name) && !empty($schedule) && !empty($class_id)) {
        $stmt = $conn->prepare("UPDATE classes SET class_name = ?, schedule = ? WHERE id = ? AND teacher_id = ?");
        $stmt->bind_param("ssii", $class_name, $schedule, $class_id, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $message = "Class updated successfully!";
        } else {
            $message = "Error updating class.";
        }
    }
} elseif ($action === 'delete' && !empty($class_id)) {
    $stmt = $conn->prepare("DELETE FROM classes WHERE id = ? AND teacher_id = ?");
    $stmt->bind_param("ii", $class_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        $message = "Class deleted successfully!";
    } else {
        $message = "Error deleting class.";
    }
}

$stmt = $conn->prepare("SELECT * FROM classes WHERE teacher_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$classes = $stmt->get_result();

if (!empty($search_query)) {
    $stmt = $conn->prepare("SELECT * FROM classes WHERE teacher_id = ? AND class_name LIKE ?");
    $search_term = '%' . $search_query . '%';
    $stmt->bind_param("is", $_SESSION['user_id'], $search_term);
    $stmt->execute();
    $search_results = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Management</title>
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

    <h1>Class Management</h1>
    <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>

    <h2>Add a Class</h2>
    <form action="class.php?action=add" method="POST">
        <label for="class_name">Class Name:</label>
        <input type="text" name="class_name" id="class_name" required>
        <br>
        <label for="schedule">Schedule:</label>
        <input type="text" name="schedule" id="schedule" required>
        <br>
        <button type="submit">Add Class</button>
    </form>

    <h2>Your Classes</h2>
    <table border="1" style="width: 100%; text-align: left;">
        <thead>
            <tr>
                <th>Class Name</th>
                <th>Schedule</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($class = $classes->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                    <td><?php echo htmlspecialchars($class['schedule']); ?></td>
                    <td>
                        <form action="class.php?action=update&id=<?php echo $class['id']; ?>" method="POST" style="display:inline;">
                            <input type="text" name="class_name" value="<?php echo htmlspecialchars($class['class_name']); ?>" required>
                            <input type="text" name="schedule" value="<?php echo htmlspecialchars($class['schedule']); ?>" required>
                            <button type="submit">Update</button>
                        </form>
                        <a href="class.php?action=delete&id=<?php echo $class['id']; ?>" onclick="return confirm('Are you sure you want to delete this class?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>Search Classes</h2>
    <form action="class.php" method="GET">
        <input type="text" name="search" placeholder="Search by name..." value="<?php echo htmlspecialchars($search_query); ?>">
        <button type="submit">Search</button>
        <a href="class.php"><button type="button">Clear</button></a>
    </form>

    <?php if (!empty($search_query)): ?>
        <h3>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h3>
        <?php if ($search_results->num_rows > 0): ?>
            <table border="1" style="width: 100%; text-align: left;">
                <thead>
                    <tr>
                        <th>Class Name</th>
                        <th>Schedule</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($result = $search_results->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($result['class_name']); ?></td>
                            <td><?php echo htmlspecialchars($result['schedule']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: red;">No classes found matching your search.</p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
