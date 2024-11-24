<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

include('db_connection.php');

$message = "";
$class_id = $_GET['class_id'] ?? '';

$class_stmt = $conn->prepare("SELECT id, class_name FROM classes WHERE teacher_id = ?");
$class_stmt->bind_param("i", $_SESSION['user_id']);
$class_stmt->execute();
$classes = $class_stmt->get_result();

$reports = [];
if (!empty($class_id)) {
    $class_check_stmt = $conn->prepare("SELECT id FROM classes WHERE id = ? AND teacher_id = ?");
    $class_check_stmt->bind_param("ii", $class_id, $_SESSION['user_id']);
    $class_check_stmt->execute();
    $class_check_result = $class_check_stmt->get_result();

    if ($class_check_result->num_rows > 0) {
        $report_stmt = $conn->prepare("
            SELECT 
                u.username AS student_name, 
                u.id AS student_id_number, 
                a.title AS assignment, 
                g.grade
            FROM grades g
            JOIN users u ON g.student_id = u.id
            JOIN assignments a ON g.assignment_id = a.id
            WHERE a.class_id = ? AND u.user_type = 'student'
            ORDER BY u.username, a.title
        ");
        $report_stmt->bind_param("i", $class_id);
        $report_stmt->execute();
        $reports = $report_stmt->get_result();
    } else {
        $message = "Invalid class or unauthorized access.";
    }
}

if (isset($_GET['export']) && $class_id) {
    $class_check_stmt = $conn->prepare("SELECT id FROM classes WHERE id = ? AND teacher_id = ?");
    $class_check_stmt->bind_param("ii", $class_id, $_SESSION['user_id']);
    $class_check_stmt->execute();
    $class_check_result = $class_check_stmt->get_result();

    if ($class_check_result->num_rows > 0) {
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=class_report.csv");

        $output = fopen("php://output", "w");
        fputcsv($output, ["Student Name", "Student ID", "Assignment", "Grade"]);

        while ($row = $reports->fetch_assoc()) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    } else {
        $message = "Invalid class or unauthorized access.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
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

    <h1>Reports</h1>
    <?php if (!empty($message)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <h2>Select a Class</h2>
    <form action="reports.php" method="GET">
        <label for="class_id">Class:</label>
        <select name="class_id" id="class_id" required>
            <option value="">Select a Class</option>
            <?php while ($class = $classes->fetch_assoc()): ?>
                <option value="<?php echo $class['id']; ?>" <?php if ($class_id == $class['id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($class['class_name']); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <br>
        <button type="submit">View Report</button>
    </form>

    <?php if (!empty($class_id)): ?>
        <h2>Class Report</h2>
        <a href="add_grades.php?class_id=<?php echo $class_id; ?>">
            <button style="padding: 10px 20px; background-color: #374d5b; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Add Grades
            </button>
        </a>
        <?php if ($reports->num_rows > 0): ?>
            <table border="1" style="width: 100%; text-align: left;">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Student ID</th>
                        <th>Assignment</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($report = $reports->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($report['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($report['student_id_number']); ?></td>
                            <td><?php echo htmlspecialchars($report['assignment']); ?></td>
                            <td><?php echo htmlspecialchars($report['grade']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <br>
            <a href="reports.php?class_id=<?php echo $class_id; ?>&export=true">
                <button style="padding: 10px 20px; background-color: #374d5b; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Export to CSV
                </button>
            </a>
        <?php else: ?>
            <p style="color: red;">No reports found for this class.</p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
