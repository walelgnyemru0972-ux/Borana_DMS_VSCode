<?php
require_once 'config.php';
require_roles(['student']);

$stmt = $pdo->prepare('SELECT s.student_id, s.first_name, s.middle_name, s.last_name, s.sex, s.batch, s.department, r.room_number, r.capacity, b.block_number, b.faculty FROM students s LEFT JOIN rooms r ON r.id=s.room_id LEFT JOIN blocks b ON b.id=r.block_id WHERE s.id=? LIMIT 1');
$stmt->execute([(int)$_SESSION['user']['id']]);
$student = $stmt->fetch();
if (!$student) {
    http_response_code(404);
    exit('Student record not found. Please contact the dormitory administrator.');
}

$page_title = 'My dorm';
require 'partials/header.php';
?>
<div class="page-heading">
  <div><p class="eyebrow">STUDENT PORTAL</p><h1>My dormitory</h1><p class="muted">Welcome, <?= h(trim($student['first_name'].' '.$student['middle_name'].' '.$student['last_name'])) ?>.</p></div>
  <span class="role-chip">Student</span>
</div>
<section class="panel">
  <h2>Your student details</h2>
  <div class="table-wrap"><table><tbody>
    <tr><th>Student ID</th><td><?= h($student['student_id']) ?></td></tr>
    <tr><th>Name</th><td><?= h(trim($student['first_name'].' '.$student['middle_name'].' '.$student['last_name'])) ?></td></tr>
    <tr><th>Sex</th><td><?= h($student['sex']) ?></td></tr>
    <tr><th>Batch / year</th><td><?= h($student['batch'] ?? '') ?></td></tr>
    <tr><th>Department</th><td><?= h($student['department']) ?></td></tr>
    <tr><th>Block</th><td><?= h($student['block_number'] ?? 'Not assigned yet') ?></td></tr>
    <tr><th>Room</th><td><?= h($student['room_number'] ?? 'Not assigned yet') ?></td></tr>
    <?php if ($student['room_number'] !== null): ?><tr><th>Room capacity</th><td><?= (int)$student['capacity'] ?> students</td></tr><?php endif; ?>
  </tbody></table></div>
</section>
<?php require 'partials/footer.php'; ?>
