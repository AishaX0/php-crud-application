<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $dob = $_POST['dob'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        header("Location: signup.html?error=Passwords do not match");
        exit();
    }

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
        header("Location: signup.html?error=Password must be at least 8 characters long, include an uppercase letter, a lowercase letter, and a number.");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        header("Location: signup.html?error=Email is already registered");
        exit();
    }
    $check_stmt->close();

    $profile_picture = "";
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $profile_picture = $target_dir . basename($_FILES["profile_picture"]["name"]);
        move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $profile_picture);
    }

    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, profile_picture, dob, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $first_name, $last_name, $email, $phone, $profile_picture, $dob, $hashed_password);

    if ($stmt->execute()) {
        header("Location: login.html?success=Signup successful! Please login.");
        exit();
    } else {
        header("Location: signup.html?error=Something went wrong, please try again.");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
