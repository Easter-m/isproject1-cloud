<?php
require __DIR__ . '/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $conn->prepare('SELECT fullname, username, email, course, created_at FROM users WHERE id = ?');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($fullname, $username, $email, $course, $created);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="card">
    <h1>Welcome, <?= e($fullname) ?></h1>
    <p class="sub">You are logged in as <?= e($username) ?>.</p>
    <dl>
        <dt>Email</dt><dd><?= e($email) ?></dd>
        <dt>Course</dt><dd><?= e($course) ?></dd>
        <dt>Registered</dt><dd><?= e($created) ?></dd>
    </dl>
    <a class="btn" href="feedback.php">Feedback</a>
    <a class="btn" href="logout.php">Log out</a>
</div>
</body>
</html>
