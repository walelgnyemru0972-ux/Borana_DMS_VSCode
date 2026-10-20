<?php
require_once 'config.php';
require_roles(['admin','manager','proctor']);
$counts = [
 'students' => (int)$pdo->query('SELECT COUNT(*) FROM students')->fetchColumn(),
 'blocks' => (int)$pdo->query('SELECT COUNT(*) FROM blocks')->fetchColumn(),
 'rooms' => (int)$pdo->query('SELECT COUNT(*) FROM rooms')->fetchColumn(),
 'assigned' => (int)$pdo->query('SELECT COUNT(*) FROM students WHERE room_id IS NOT NULL')->fetchColumn(),
];
$recent = $pdo->query('SELECT s.student_id, s.first_name, s.middle_name, s.last_name, s.sex, s.department, r.room_number, b.block_number FROM students s LEFT JOIN rooms r ON s.room_id=r.id LEFT JOIN blocks b ON r.block_id=b.id ORDER BY s.created_at DESC LIMIT 8')->fetchAll();
$page_title = 'Dashboard';
require 'partials/header.php';
?>
<div class="page-heading"><div><p class="eyebrow">OVERVIEW</p><h1>Dashboard</h1><p class="muted">Welcome, <?= h($_SESSION['user']['full_name']) ?>.</p></div><span class="role-chip"><?= h(ucfirst(current_role())) ?></span></div>
<div class="stats-grid">
  <article class="stat-card"><span>Students</span><strong><?= $counts['students'] ?></strong><small>Total student records</small></article>
  <article class="stat-card"><span>Blocks</span><strong><?= $counts['blocks'] ?></strong><small>Registered blocks</small></article>
  <article class="stat-card"><span>Rooms</span><strong><?= $counts['rooms'] ?></strong><small>Registered rooms</small></article>
  <article class="stat-card"><span>Assigned</span><strong><?= $counts['assigned'] ?></strong><small>Students with a room</small></article>
</div>
<div class="page-heading section-top"><div><h2>Recent student records</h2><p class="muted">Latest records in the system.</p></div>
<?php if (in_array(current_role(), ['admin','manager','proctor'], true)): ?><a class="button" href="students.php">Manage students</a><?php endif; ?></div>
<div class="table-wrap"><table><thead><tr><th>Student ID</th><th>Name</th><th>Sex</th><th>Department</th><th>Block</th><th>Room</th></tr></thead><tbody>
<?php foreach ($recent as $s): ?><tr><td><?= h($s['student_id']) ?></td><td><?= h(trim($s['first_name'].' '.$s['middle_name'].' '.$s['last_name'])) ?></td><td><?= h($s['sex']) ?></td><td><?= h($s['department']) ?></td><td><?= h($s['block_number'] ?? 'Not assigned') ?></td><td><?= h($s['room_number'] ?? 'Not assigned') ?></td></tr><?php endforeach; ?>
<?php if (!$recent): ?><tr><td colspan="6" class="empty">No students yet. Add student records to get started.</td></tr><?php endif; ?>
</tbody></table></div>
<?php require 'partials/footer.php'; ?>
