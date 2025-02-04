<?php
session_start();
include 'db.php';

$is_logged_in = isset($_SESSION['user_id']);

if ($is_logged_in) {
    $user_id = $_SESSION['user_id'];

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_task'])) {
        $task = trim($_POST['task']);
        if (!empty($task)) {
            $stmt = $conn->prepare("INSERT INTO tasks (user_id, task) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $task);
            if ($stmt->execute()) {
                header("Location: index.php"); 
                exit();
            }
            $stmt->close();
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_task'])) {
        $task_id = $_POST['task_id'];
        $task = trim($_POST['task']);
        if (!empty($task)) {
            $stmt = $conn->prepare("UPDATE tasks SET task=? WHERE id=? AND user_id=?");
            $stmt->bind_param("sii", $task, $task_id, $user_id);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php");
            exit();
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_task'])) {
        $task_id = $_POST['task_id'];
        $stmt = $conn->prepare("DELETE FROM tasks WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $task_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

$tasks = [];
if ($is_logged_in) {
    $result = $conn->query("SELECT * FROM tasks WHERE user_id = $user_id");
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    <?php include 'header.php'; ?>

    <div class="container my-5 bg-white p-5 rounded-lg shadow-sm flex-grow-1">
        <h1 class="text-center text-success mb-4">My Notes</h1>
        <p class="text-center text-muted mb-4">Your daily notes organized.</p>

        <?php if ($is_logged_in): ?>
            <form method="POST" class="mb-4 d-flex">
                <input type="text" name="task" placeholder="Add New Note" class="form-control mr-2" required>
                <button type="submit" name="add_task" class="btn btn-success">
                    <i class="fas fa-plus"></i>
                </button>
            </form>

            <div class="list-group">
                <?php if (!empty($tasks)): ?>
                    <?php foreach ($tasks as $task): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <form method="POST" style="display: inline;" class="d-flex w-100">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">

                                <?php if (isset($_GET['edit']) && $_GET['edit'] == $task['id']): ?>
                                    <div class="d-flex w-100">
                                        <input type="text" name="task" value="<?php echo htmlspecialchars($task['task']); ?>" class="form-control mr-2" required>
                                        <button type="submit" name="edit_task" class="btn btn-success ml-2">Save</button>
                                        <button type="submit" name="delete_task" class="btn btn-danger ml-2" onclick="return confirm('Are you sure you want to delete this task?');">Delete</button>
                                    </div>
                                <?php else: ?>
                                    <span class="flex-grow-1"><?php echo htmlspecialchars($task['task']); ?></span>
                                    <a href="index.php?edit=<?php echo $task['id']; ?>" class="btn btn-link text-success">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                <?php endif; ?>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No notes available. Add a new note !</p>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <div class="text-center mb-4">
            <img src="images/home.png" alt="Welcome Image" class="img-fluid" style="width: 50%; height: auto;">


            </div>

            <p class="text-muted text-center">Please <a href="login.html" class="text-success">Log In</a> or <a href="signup.html" class="text-success">Sign Up</a> to manage your notes.</p>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
