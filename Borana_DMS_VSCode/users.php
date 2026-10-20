<?php
require_once 'config.php';
require_roles(['admin']);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $full_name = trim((string)($_POST['full_name'] ?? ''));
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $role = (string)($_POST['role'] ?? '');
    if ($full_name === '' || $username === '' || strlen($password) < 8 || !in_array($role, ['admin','manager','proctor'], true)) {
        $error = 'Enter all fields, select a role, and use a password with at least 8 characters.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO users (full_name, username, password_hash, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$full_name, $username, password_hash($password, PASSWORD_DEFAULT), $role]);
            flash('User account created.');
            redirect('users.php');
        } catch (PDOException $e) {
            $error = 'Could not create account. Username must be unique.';
        }
    }
}
$users = $pdo->query('SELECT id, full_name, username, role, active, created_at FROM users ORDER BY created_at DESC')->fetchAll();
$page_title = 'User accounts';
require 'partials/header.php';
?>
<div class="page-heading"><div><p class="eyebrow">SYSTEM ADMINISTRATION</p><h1>User accounts</h1><p class="muted">Create accounts for proctor managers and proctors.</p></div></div>
<?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
<div class="content-grid">
<section class="panel"><h2>Create account</h2><form method="post" class="form-stack"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
<label>Full name<input name="full_name" required></label><label>Username<input name="username" required></label><label>Password (minimum 8 characters)<input type="password" name="password" minlength="8" required></label>
<label>Role<select name="role" required><option value="">Choose role...</option><option value="manager">Proctor manager</option><option value="proctor">Proctor</option><option value="admin">Administrator</option></select></label><button class="button">Create account</button></form></section>
<section class="panel"><h2>Existing accounts</h2><div class="table-wrap"><table><thead><tr><th>Name</th><th>Username</th><th>Role</th><th>Status</th></tr></thead><tbody><?php foreach ($users as $u): ?><tr><td><?= h($u['full_name']) ?></td><td><?= h($u['username']) ?></td><td><?= h($u['role'] === 'manager' ? 'Proctor manager' : $u['role']) ?></td><td><?= (int)$u['active'] ? 'Active' : 'Inactive' ?></td></tr><?php endforeach; ?></tbody></table></div></section>
</div>
<?php require 'partials/footer.php'; ?>
