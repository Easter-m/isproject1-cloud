<?php
require __DIR__ . '/config.php';

const MAX_ATTEMPTS = 5;
const LOCK_MINUTES = 10;

$error = '';
$username = '';
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $attemptName = mb_substr($username, 0, 50);

    // How many recent failures for this username from this IP?
    $stmt = $conn->prepare(
        'SELECT COUNT(*) FROM login_attempts
         WHERE username = ? AND ip = ?
         AND attempted_at > (NOW() - INTERVAL ' . LOCK_MINUTES . ' MINUTE)'
    );
    $stmt->bind_param('ss', $attemptName, $ip);
    $stmt->execute();
    $stmt->bind_result($recent);
    $stmt->fetch();
    $stmt->close();

    if ($recent >= MAX_ATTEMPTS) {
        $error = 'Too many failed attempts. Try again in ' . LOCK_MINUTES . ' minutes.';
    } else {
        $stmt = $conn->prepare('SELECT id, password_hash FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->bind_result($id, $hash);
        $found = $stmt->fetch();
        $stmt->close();

        if ($found && password_verify($password, $hash)) {
            // Success: clear the failure count, then log in
            $stmt = $conn->prepare('DELETE FROM login_attempts WHERE username = ? AND ip = ?');
            $stmt->bind_param('ss', $attemptName, $ip);
            $stmt->execute();
            $stmt->close();

            session_regenerate_id(true);
            $_SESSION['user_id'] = $id;
            header('Location: welcome.php');
            exit;
        }

        // Failure: record it
        $stmt = $conn->prepare('INSERT INTO login_attempts (username, ip) VALUES (?, ?)');
        $stmt->bind_param('ss', $attemptName, $ip);
        $stmt->execute();
        $stmt->close();

        $error = 'Wrong username or password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="card">
    <h1>Log in</h1>
    <p class="sub">Use the account you registered.</p>

    <?php if (isset($_GET['registered'])): ?>
        <div class="msg success">Account created. You can log in now.</div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="msg error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <label for="username">Username</label>
        <input id="username" name="username" value="<?= e($username) ?>" required>

        <label for="password">Password</label>
        <input id="password" name="password" type="password" required>

        <button type="submit">Log in</button>
    </form>
    <p class="alt">No account yet? <a href="signup.php">Create one</a></p>
</div>
</body>
</html>
