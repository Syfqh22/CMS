<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

include('db_connection.php');

$message = "";
$action = $_GET['action'] ?? '';
$bulletin_id = $_GET['id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bulletin_title = $_POST['bulletin_title'] ?? '';
    $bulletin_content = $_POST['bulletin_content'] ?? '';

    if ($action === 'add' && !empty($bulletin_title) && !empty($bulletin_content)) {
        $stmt = $conn->prepare("INSERT INTO bulletins (title, content, teacher_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $bulletin_title, $bulletin_content, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $message = "Bulletin added successfully!";
        } else {
            $message = "Error adding bulletin.";
        }
    }
} elseif ($action === 'delete' && !empty($bulletin_id)) {
    $stmt = $conn->prepare("DELETE FROM bulletins WHERE id = ? AND teacher_id = ?");
    $stmt->bind_param("ii", $bulletin_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        $message = "Bulletin deleted successfully!";
    } else {
        $message = "Error deleting bulletin.";
    }
}

$stmt = $conn->prepare("SELECT * FROM bulletins WHERE teacher_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$bulletins = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin Management</title>
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

    <h1>Bulletin Management</h1>
    <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>

    <h2>Create a Bulletin</h2>
    <form action="bulletin_t.php?action=add" method="POST">
        <label for="bulletin_title">Title:</label>
        <input type="text" name="bulletin_title" id="bulletin_title" required>
        <br>
        <label for="bulletin_content">Content:</label>
        <textarea name="bulletin_content" id="bulletin_content" rows="5" required></textarea>
        <br>
        <button type="submit">Add Bulletin</button>
    </form>

    <h2>Your Bulletins</h2>
    <table border="1" style="width: 100%; text-align: left;">
        <thead>
            <tr>
                <th>Title</th>
                <th>Content</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($bulletin = $bulletins->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($bulletin['title']); ?></td>
                    <td><?php echo htmlspecialchars($bulletin['content']); ?></td>
                    <td>
                        <a href="bulletin_t.php?action=delete&id=<?php echo $bulletin['id']; ?>" onclick="return confirm('Are you sure you want to delete this bulletin?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
