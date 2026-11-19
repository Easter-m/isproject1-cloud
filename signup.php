<?php
require __DIR__ . '/config.php';

$errors = [];
$fullname = $username = $email = $course = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $course   = trim($_POST['course'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if ($fullname === '' || $username === '' || $email === '' || $course === '' || $password === '') {
        $errors[] = 'Fill in every field.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
        if (strlen($password) < 8) {
        $errors[] = 'Use a password of at least 8 characters.';
    }
    if (strlen($password) > 72) {
        $errors[] = 'Use a password of at most 72 characters.';
    }
    if (!preg_match('/[a-z]/', $password) || !preg_match('/[A-Z]/', $password)
        || !preg_match('/[0-9]/', $password) || !preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errors[] = 'Password needs a lowercase letter, an uppercase letter, a number and a symbol.';
    }
    if ($username !== '' && stripos($password, $username) !== false) {
        $errors[] = 'Password must not contain your username.';
    }
    if ($password !== $confirm) {
        $errors[] = 'The two passwords do not match.';
    }

    if (!$errors) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'That username or email is already registered.';
        }
        $stmt->close();
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (fullname, username, email, course, password_hash) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $fullname, $username, $email, $course, $hash);
        $stmt->execute();
        $stmt->close();
        header('Location: login.php?registered=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create account</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="card">
    <h1>Create account</h1>
    <p class="sub">Register to use the site.</p>

    <?php foreach ($errors as $err): ?>
        <div class="msg error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post" action="signup.php">
        <label for="fullname">Full name</label>
        <input id="fullname" name="fullname" value="<?= e($fullname) ?>" required>

        <label for="username">Username</label>
        <input id="username" name="username" value="<?= e($username) ?>" required>

        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="<?= e($email) ?>" required>

        <label for="course">Course</label>
        <input id="course" name="course" value="<?= e($course) ?>" required>

                <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="new-password" required>
        <div id="strength" class="strength strength-0">
            <div class="strength-bar"></div>
            <span class="strength-text">Enter a password</span>
        </div>

        <label for="confirm">Confirm password</label>
        <input id="confirm" name="confirm" type="password" required>

        <button type="submit">Create account</button>
    </form>
    <p class="alt">Already registered? <a href="login.php">Log in</a></p>
</div>
<script src="strength.js"></script>
</body>
</html>
