<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, phone, profile_picture, dob, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['phone'] = $user['phone'];
            $_SESSION['dob'] = $user['dob'];
            $_SESSION['profile_picture'] = $user['profile_picture'];

            header("Location: index.php");
            exit();
        } else {
            header("Location: login.html?error=Invalid email or password");
            exit();
        }
    } else {
        header("Location: login.html?error=User not found");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
