<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Add Task
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_task'])) {
    $task = trim($_POST['task']);
    
    if (!empty($task)) {
        $stmt = $conn->prepare("INSERT INTO tasks (user_id, task) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $task);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: tasks.php");
    exit();
}

// Edit Task
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_task'])) {
    $task_id = $_POST['task_id'];
    $updated_task = trim($_POST['task']);
    
    if (!empty($updated_task)) {
        $stmt = $conn->prepare("UPDATE tasks SET task=? WHERE id=? AND user_id=?");
        $stmt->bind_param("sii", $updated_task, $task_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: tasks.php");
    exit();
}

// Delete Task
if (isset($_GET['delete'])) {
    $task_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: tasks.php");
    exit();
}

// Fetch All Tasks for Logged-in User
$result = $conn->query("SELECT * FROM tasks WHERE user_id = $user_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tasks</title>
</head>
<body>
    <?php include 'header.php'; ?>

    <h2>Your Tasks</h2>

    <!-- Add Task Form -->
    <form action="tasks.php" method="POST">
        <input type="text" name="task" placeholder="Enter a new task" required>
        <button type="submit" name="add_task">Add Task</button>
    </form>

    <ul>
        <?php while ($task = $result->fetch_assoc()): ?>
            <li>
                <?php echo htmlspecialchars($task['task']); ?>
                
                <!-- Edit Task Form -->
                <form action="tasks.php" method="POST" style="display:inline;">
                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                    <input type="text" name="task" value="<?php echo htmlspecialchars($task['task']); ?>" required>
                    <button type="submit" name="edit_task">Edit</button>
                </form>

                <!-- Delete Task Button -->
                <a href="tasks.php?delete=<?php echo $task['id']; ?>" onclick="return confirm('Are you sure you want to delete this task?');">Delete</a>
            </li>
        <?php endwhile; ?>
    </ul>

    <?php include 'footer.php'; ?>
</body>
</html>
