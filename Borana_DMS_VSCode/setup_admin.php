<?php
require_once __DIR__ . '/config.php';
$page_title = 'Create admin account';
$notice = '';
$warning = '';

try {
    $exists = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE username = 'admin'")->fetchColumn();
    if ($exists > 0) {
        $notice = 'Administrator account already exists. Use the existing admin login at <a href="login.php">login.php</a>.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO users (full_name, username, password_hash, role, active) VALUES (?, ?, ?, ?, 1)');
        $stmt->execute([
            'Administrator',
            'admin',
            password_hash('Admin@12345', PASSWORD_DEFAULT),
            'admin',
        ]);
        $notice = 'Admin account created successfully. Username: <strong>admin</strong> | Password: <strong>Admin@12345</strong>.<br><a href="login.php">Go to login</a>';
    }
} catch (Throwable $e) {
    $warning = 'Could not create the admin user. Make sure the database has been imported and the MySQL server is running. Error: ' . h($e->getMessage());
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($page_title) ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7fb; margin: 0; padding: 40px; }
        .card { max-width: 640px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
        h1 { margin-top: 0; }
        .success { color: #0a6a39; background: #eafaf1; border: 1px solid #bfe9d1; padding: 12px 14px; border-radius: 8px; }
        .warning { color: #8a4b00; background: #fff3d9; border: 1px solid #f7d79d; padding: 12px 14px; border-radius: 8px; }
        a { color: #0a62d0; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Admin setup</h1>
        <?php if ($notice): ?>
            <div class="success"><?= $notice ?></div>
        <?php endif; ?>
        <?php if ($warning): ?>
            <div class="warning"><?= h($warning) ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
