<?php
$page_title = $page_title ?? 'Borana University Dormitory Management';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($page_title) ?></title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="index.php"><span class="brand-mark">BU</span><span>Borana University<br><small>Dormitory Management System</small></span></a>
  <nav>
    <a href="index.php">Home</a>
    <?php if (is_logged_in()): ?>
      <?php if (current_role() === 'student'): ?>
        <a href="student_dorm.php">My dorm</a>
      <?php else: ?>
        <a href="dashboard.php">Dashboard</a>
      <?php endif; ?>
      <?php if (in_array(current_role(), ['admin','manager'], true)): ?>
        <a href="blocks.php">Blocks</a><a href="rooms.php">Rooms</a><a href="students.php">Students</a>
        <?php if (current_role() === 'admin'): ?><a href="users.php">User accounts</a><?php endif; ?>
      <?php elseif (current_role() === 'proctor'): ?>
        <a href="students.php">Students</a><a href="rooms.php">Rooms</a>
      <?php endif; ?>
      <span class="nav-user"><?= h($_SESSION['user']['full_name'] ?? '') ?></span>
      <a class="nav-logout" href="logout.php">Log out</a>
    <?php else: ?>
      <a class="nav-login" href="login.php">Login</a>
    <?php endif; ?>
  </nav>
</header>
<main class="container">
<?php if (!empty($_SESSION['flash'])): $f = $_SESSION['flash']; unset($_SESSION['flash']); ?>
  <div class="alert <?= h($f['type']) ?>"><?= h($f['message']) ?></div>
<?php endif; ?>
