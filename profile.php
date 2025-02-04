<?php include 'header.php'; ?>
<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT first_name, last_name, email, phone, dob, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $dob = $_POST['dob'];
    $profile_picture = $user['profile_picture']; 

    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check_stmt->bind_param("si", $email, $user_id);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        header("Location: profile.php?error=Email is already in use.");
        exit();
    }
    $check_stmt->close();

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $profile_picture = $target_dir . basename($_FILES["profile_picture"]["name"]);
        move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $profile_picture);
    }

    $update_stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, phone=?, dob=?, profile_picture=? WHERE id=?");
    $update_stmt->bind_param("ssssssi", $first_name, $last_name, $email, $phone, $dob, $profile_picture, $user_id);

    if ($update_stmt->execute()) {
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;
        $_SESSION['email'] = $email;
        $_SESSION['phone'] = $phone;
        $_SESSION['dob'] = $dob;
        $_SESSION['profile_picture'] = $profile_picture;

        header("Location: profile.php?success=Profile updated successfully!");
        exit();
    } else {
        header("Location: profile.php?error=Something went wrong. Please try again.");
        exit();
    }

    $update_stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_account'])) {
    $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $delete_stmt->bind_param("i", $user_id);

    if ($delete_stmt->execute()) {
        session_destroy();
        header("Location: index.php?message=Account deleted successfully.");
        exit();
    } else {
        header("Location: profile.php?error=Failed to delete account.");
        exit();
    }

    $delete_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .btn-custom {
            background-color: #28a745;
            color: white;
        }
        .btn-custom:hover {
            background-color: #218838;
        }
        .card-custom {
            border-radius: 12px;
            border: 2px solid #28a745;
        }
        .profile-header {
            background-color: #28a745;
            color: white;
            padding: 15px;
            border-radius: 12px 12px 0 0;
            font-size: 24px;
        }
        .form-group label {
            color: #28a745;
        }
    </style>
</head>
<body class="bg-light">

    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow-lg card-custom p-5" style="max-width: 600px; width: 100%;">

            <div class="profile-header text-center">
                My Profile
            </div>

            <div class="text-center mb-4">
                <img src="<?php echo !empty($user['profile_picture']) ? $user['profile_picture'] : 'default-avatar.png'; ?>" alt="Profile Picture" class="rounded-circle" style="width: 150px; height: 150px;">
            </div>

            <?php if (isset($_GET['error'])): ?>
                <p class="text-danger text-center mb-4"><?php echo htmlspecialchars($_GET['error']); ?></p>
            <?php elseif (isset($_GET['success'])): ?>
                <p class="text-success text-center mb-4"><?php echo htmlspecialchars($_GET['success']); ?></p>
            <?php endif; ?>

            <form action="profile.php" method="POST" enctype="multipart/form-data">

                <div class="form-row mb-4">
                    <div class="form-group col-md-6">
                        <label for="first_name">First Name:</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" class="form-control" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" class="form-control" required>
                    </div>
                </div>

                <div class="form-row mb-4">
                    <div class="form-group col-md-6">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="phone">Phone Number:</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" class="form-control" required>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label for="dob">Date of Birth:</label>
                    <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($user['dob']); ?>" class="form-control" required>
                </div>

                <div class="form-group mb-4">
                    <label for="profile_picture">Profile Picture:</label>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="form-control">
                </div>

                <button type="submit" name="update_profile" class="btn btn-custom btn-block py-2">Update Profile</button>
            </form>

            <form action="profile.php" method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                <button type="submit" name="delete_account" class="btn btn-danger btn-block py-2 mt-4">Delete Account</button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>

<?php include 'footer.php'; ?>
