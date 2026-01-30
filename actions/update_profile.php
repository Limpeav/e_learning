<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $bio = $_POST['bio'];
    
    $stmt = $pdo->prepare("SELECT password, avatar FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    $avatar_name = $user['avatar'];

    // Handle File Upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../public/uploads/avatars/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid('avatar_') . '.' . $extension;
        $destination = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
            $avatar_name = $new_filename;
        }
    }

    // Update Basic Info
    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, bio = ?, avatar = ? WHERE id = ?");
    $stmt->execute([$username, $email, $bio, $avatar_name, $user_id]);
    $_SESSION['username'] = $username;

    // Password Update logic
    if (!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
        if (password_verify($_POST['current_password'], $user['password'])) {
            $hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $user_id]);
        }
    }

    header('Location: ../views/auth/profile.php?success=1');
} else {
    header('Location: ../index.php');
}
exit();
