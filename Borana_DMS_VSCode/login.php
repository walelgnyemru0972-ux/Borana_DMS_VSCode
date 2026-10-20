<?php
require_once 'config.php';
if (is_logged_in()) {
    redirect(current_role() === 'student' ? 'student_dorm.php' : 'dashboard.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $stmt = $pdo->prepare('SELECT id, full_name, username, password_hash, role, active FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && (int)$user['active'] === 1 && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'full_name' => $user['full_name'],
            'username' => $user['username'],
            'role' => $user['role'],
        ];
        redirect('dashboard.php');
    }
    $stmt = $pdo->prepare('SELECT id, student_id, first_name, middle_name, last_name, password_hash FROM students WHERE first_name = ? AND student_id = ? LIMIT 1');
    $stmt->execute([$username, $password]);
    $student = $stmt->fetch();
    if (!$student) {
        $stmt = $pdo->prepare('SELECT id, student_id, first_name, middle_name, last_name, password_hash FROM students WHERE student_id = ? LIMIT 1');
        $stmt->execute([$username]);
        $student = $stmt->fetch();
    }
    if ($student && $student['password_hash'] !== null && password_verify($password, $student['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$student['id'],
            'full_name' => trim($student['first_name'] . ' ' . ($student['middle_name'] ?? '') . ' ' . $student['last_name']),
            'username' => $student['student_id'],
            'role' => 'student',
        ];
        redirect('student_dorm.php');
    }
    $error = 'Invalid username or password, or the account is inactive.';
}
$page_title = 'Login';
require 'partials/header.php';
?>
<div class="auth-wrap">
  <section class="auth-card">
    <p class="eyebrow">WELCOME BACK</p><h1>Sign in</h1>
    <p class="muted">Staff can use their username; students can use their student ID.</p>
    <?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
    <form method="post" class="form-stack" autocomplete="off">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
      <label>First name or student ID<input name="username" placeholder="Enter your name" autocomplete="off" required autofocus></label>
      <label>Password<input type="password" name="password" placeholder="Enter your password" autocomplete="new-password" required></label>
      <button class="button full" type="submit">Login securely</button>
    </form>

</div>
<?php require 'partials/footer.php'; ?>
