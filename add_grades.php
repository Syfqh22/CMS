<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

include('db_connection.php');

$message = "";
$class_id = $_GET['class_id'] ?? '';

function calculate_grade($score) {
    if ($score >= 90) return 'A+';
    if ($score >= 80) return 'A';
    if ($score >= 70) return 'B';
    if ($score >= 60) return 'C';
    if ($score >= 50) return 'D';
    return 'F';
}

if (!empty($class_id)) {
    $class_check_stmt = $conn->prepare("SELECT id, teacher_id FROM classes WHERE id = ? AND teacher_id = ?");
    $class_check_stmt->bind_param("ii", $class_id, $_SESSION['user_id']);
    $class_check_stmt->execute();
    $class_check_result = $class_check_stmt->get_result();

    if ($class_check_result->num_rows === 0) {
        $message = "Class not found or you do not have permission to access it.";
    } else {
        $assignments_stmt = $conn->prepare("SELECT id, title FROM assignments WHERE class_id = ?");
        $assignments_stmt->bind_param("i", $class_id);
        $assignments_stmt->execute();
        $assignments = $assignments_stmt->get_result();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($class_id)) {
    $student_id = $_POST['student_id'] ?? '';
    $assignment_id = $_POST['assignment_id'] ?? '';
    $grade = $_POST['grade'] ?? '';

    if (is_numeric($grade)) {
        $letter_grade = calculate_grade($grade);
    } else {
        $letter_grade = $grade; 
    }

    if ($student_id && $assignment_id && $grade) {
        $insert_grade_stmt = $conn->prepare("
            INSERT INTO grades (student_id, assignment_id, grade)
            VALUES (?, ?, ?)
        ");
        $insert_grade_stmt->bind_param("iis", $student_id, $assignment_id, $letter_grade);
        $insert_grade_stmt->execute();

        $message = "Grade added successfully!";
    } else {
        $message = "Please fill all fields.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Grades</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div style="margin-bottom: 20px;">
        <a href="reports.php?class_id=<?php echo $class_id; ?>">
            <button style="padding: 10px 20px; background-color: #374d5b; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Back to Reports
            </button>
        </a>
    </div>

    <h1>Add Grades</h1>

    <?php if (!empty($message)): ?>
        <p style="color: <?php echo (strpos($message, 'successfully') !== false) ? 'green' : 'red'; ?>;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if (empty($message) || strpos($message, 'successfully') !== false): ?>
        <h2>Select Assignment and Student</h2>

        <form action="add_grades.php?class_id=<?php echo $class_id; ?>" method="POST">
            <label for="assignment_id">Assignment:</label>
            <select name="assignment_id" id="assignment_id" required>
                <option value="">Select an Assignment</option>
                <?php while ($assignment = $assignments->fetch_assoc()): ?>
                    <option value="<?php echo $assignment['id']; ?>"><?php echo htmlspecialchars($assignment['title']); ?></option>
                <?php endwhile; ?>
            </select>
            <br><br>

            <label for="student_id">Student:</label>
            <select name="student_id" id="student_id" required>
                <option value="">Select a Student</option>
                <?php
                $students_stmt = $conn->prepare("SELECT u.id, u.username FROM users u
                    JOIN student_classes sc ON sc.student_id = u.id WHERE sc.class_id = ?");
                $students_stmt->bind_param("i", $class_id);
                $students_stmt->execute();
                $students = $students_stmt->get_result();

                while ($student = $students->fetch_assoc()): ?>
                    <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['username']); ?></option>
                <?php endwhile; ?>
            </select>
            <br><br>

            <label for="grade">Grade:</label>
            <input type="text" name="grade" id="grade" required>
            <br><br>

            <button type="submit">Add Grade</button>
        </form>
    <?php endif; ?>
</body>
</html>
