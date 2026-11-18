<?php
require __DIR__ . '/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$errors = [];
$subject = $message = '';
$rating = 5;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $rating  = (int)($_POST['rating'] ?? 0);

    if ($subject === '' || $message === '') {
        $errors[] = 'Fill in the subject and the message.';
    }
    if ($rating < 1 || $rating > 5) {
        $errors[] = 'Pick a rating from 1 to 5.';
    }

    if (!$errors) {
        $stmt = $conn->prepare('INSERT INTO feedback (user_id, subject, rating, message) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('isis', $_SESSION['user_id'], $subject, $rating, $message);
        $stmt->execute();
        $stmt->close();
        header('Location: feedback.php?saved=1');
        exit;
    }
}

$rows = $conn->query('SELECT f.subject, f.rating, f.message, f.created_at, u.username
                      FROM feedback f JOIN users u ON u.id = f.user_id
                      ORDER BY f.id DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Feedback</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; font-size: 0.9rem; }
        th, td { text-align: left; padding: 0.5rem; border-bottom: 1px solid #d5dde2; vertical-align: top; }
        th { color: #5a6b75; }
        textarea { width: 100%; padding: 0.6rem 0.7rem; border: 1px solid #b9c5cc; border-radius: 4px; font: inherit; }
    </style>
</head>
<body>
<div class="card" style="max-width: 48rem;">
    <h1>Feedback</h1>
    <p class="sub">Send a short note about the course.</p>

    <?php if (isset($_GET['saved'])): ?>
        <div class="msg success">Feedback saved.</div>
    <?php endif; ?>
    <?php foreach ($errors as $err): ?>
        <div class="msg error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post" action="feedback.php">
        <label for="subject">Subject</label>
        <input id="subject" name="subject" value="<?= e($subject) ?>" required>

        <label for="rating">Rating (1 to 5)</label>
        <input id="rating" name="rating" type="number" min="1" max="5" value="<?= e($rating) ?>" required>

        <label for="message">Message</label>
        <textarea id="message" name="message" rows="4" required><?= e($message) ?></textarea>

        <button type="submit">Save feedback</button>
    </form>

    <h2 style="margin-top:1.5rem;font-size:1.1rem;">Saved feedback</h2>
    <table>
        <tr><th>User</th><th>Subject</th><th>Rating</th><th>Message</th><th>Saved</th></tr>
        <?php while ($r = $rows->fetch_assoc()): ?>
        <tr>
            <td><?= e($r['username']) ?></td>
            <td><?= e($r['subject']) ?></td>
            <td><?= e($r['rating']) ?></td>
            <td><?= e($r['message']) ?></td>
            <td><?= e($r['created_at']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <p class="alt"><a href="welcome.php">Back to welcome page</a></p>
</div>
</body>
</html>
