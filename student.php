<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

include('db_connection.php');

$message = "";
$action = $_GET['action'] ?? '';
$student_id = $_GET['id'] ?? '';
$search_query = $_GET['search'] ?? '';

$sql = "SELECT * FROM classes WHERE teacher_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$classes_result = $stmt->get_result();
$classes = $classes_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$sql = "
    SELECT users.*, GROUP_CONCAT(classes.class_name SEPARATOR ', ') AS class_names
    FROM users
    LEFT JOIN student_classes ON users.id = student_classes.student_id
    LEFT JOIN classes ON student_classes.class_id = classes.id
    WHERE users.user_type = 'student'
    GROUP BY users.id";
$result = $conn->query($sql);
$students = $result->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $sql = "INSERT INTO users (username, email, password, user_type) VALUES (?, ?, ?, 'student')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        $message = "Student added successfully.";
        header("Location: {$_SERVER['PHP_SELF']}");
        exit;
    } else {
        $message = "Error adding student: " . $conn->error;
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_class'])) {
    $student_id = $_POST['student_id'];
    $class_id = $_POST['class_id'];

    $sql = "INSERT IGNORE INTO student_classes (student_id, class_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $student_id, $class_id);

    if ($stmt->execute()) {
        $message = "Class assigned successfully.";
        header("Location: {$_SERVER['PHP_SELF']}");
        exit;
    } else {
        $message = "Error assigning class: " . $conn->error;
    }
    $stmt->close();
}

if ($action === 'delete' && !empty($student_id)) {
    $sql = "DELETE FROM users WHERE id = ? AND user_type = 'student'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);

    if ($stmt->execute()) {
        $message = "Student deleted successfully.";
        header("Location: {$_SERVER['PHP_SELF']}");
        exit;
    } else {
        $message = "Error deleting student: " . $conn->error;
    }
    $stmt->close();
}

if (!empty($search_query)) {
    $sql = "
        SELECT users.*, GROUP_CONCAT(classes.class_name SEPARATOR ', ') AS class_names
        FROM users
        LEFT JOIN student_classes ON users.id = student_classes.student_id
        LEFT JOIN classes ON student_classes.class_id = classes.id
        WHERE users.user_type = 'student' AND users.username LIKE ?
        GROUP BY users.id";
    $stmt = $conn->prepare($sql);
    $search_param = "%" . $search_query . "%";
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    $students = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management</title>
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

    <h1>Student Management</h1>
    <p><?php echo $message; ?></p>

    <h2>Add Student</h2>
    <form method="POST">
        <label for="username">Name:</label>
        <input type="text" name="username" required>
        <label for="email">Email:</label>
        <input type="email" name="email" required>
        <label for="password">Password:</label>
        <input type="password" name="password" required>
        <button type="submit" name="add_student">Add Student</button>
    </form>

    <h2>Assign Class</h2>
    <form method="POST">
        <label for="student_id">Select Student:</label>
        <select name="student_id" required>
            <option value="">Select Student</option>
            <?php foreach ($students as $student): ?>
                <option value="<?php echo $student['id']; ?>"><?php echo $student['username']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="class_id">Select Class:</label>
        <select name="class_id" required>
            <option value="">Select Class</option>
            <?php foreach ($classes as $class): ?>
                <option value="<?php echo $class['id']; ?>"><?php echo $class['class_name']; ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" name="assign_class">Assign Class</button>
    </form>

    <h2>Search Students</h2>
    <form method="GET">
        <input type="text" name="search" placeholder="Search by name" value="<?php echo htmlspecialchars($search_query); ?>">
        <button type="submit">Search</button>
    </form>

    <h2>Students</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Classes</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?php echo $student['id']; ?></td>
                    <td><?php echo htmlspecialchars($student['username']); ?></td>
                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                    <td><?php echo htmlspecialchars($student['class_names'] ?? 'Unassigned'); ?></td>
                    <td>
                        <a href="?action=delete&id=<?php echo $student['id']; ?>" onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
