<?php
require_once 'config.php';
require_roles(['admin','manager','proctor']);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array(current_role(), ['admin','manager','proctor'], true)) {
    verify_csrf();
    $action = (string)($_POST['action'] ?? 'create');
    if ($action === 'set_password') {
        $student_pk = filter_input(INPUT_POST, 'student_pk', FILTER_VALIDATE_INT);
        $password = (string)($_POST['password'] ?? '');
        if (!$student_pk || strlen($password) < 8) {
            $error = 'Choose a student and enter a password with at least 8 characters.';
        } else {
            $stmt = $pdo->prepare('UPDATE students SET password_hash=? WHERE id=?');
            $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $student_pk]);
            if ($stmt->rowCount() !== 1) {
                $error = 'Could not set the student password. Refresh the page and try again.';
            } else {
                flash('Student login password saved.');
                redirect('students.php');
            }
        }
    } elseif ($action === 'assign') {
        $student_id = filter_input(INPUT_POST, 'student_pk', FILTER_VALIDATE_INT);
        $room_id = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
        if (!$student_id || !$room_id) {
            $error = 'Select a student and a room.';
        } else {
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('SELECT id, sex FROM students WHERE id=? FOR UPDATE');
                $stmt->execute([$student_id]); $student = $stmt->fetch();
                $stmt = $pdo->prepare('SELECT r.id, r.capacity, b.sex_category, (SELECT COUNT(*) FROM students s WHERE s.room_id=r.id) AS occupants FROM rooms r JOIN blocks b ON b.id=r.block_id WHERE r.id=? FOR UPDATE');
                $stmt->execute([$room_id]); $room = $stmt->fetch();
                if (!$student || !$room) throw new RuntimeException('Student or room was not found.');
                if ($student['sex'] !== $room['sex_category']) throw new RuntimeException('Assignment blocked: student sex must match the block sex category.');
                if ((int)$room['occupants'] >= (int)$room['capacity']) throw new RuntimeException('Assignment blocked: this room is full.');
                $stmt = $pdo->prepare('UPDATE students SET room_id=? WHERE id=?');
                $stmt->execute([$room_id, $student_id]);
                $pdo->commit();
                flash('Student assigned to room successfully.');
                redirect('students.php');
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = $e instanceof RuntimeException ? $e->getMessage() : 'Could not assign student. Check the room and student records.';
            }
        }
    } elseif ($action === 'create' && in_array(current_role(), ['admin','manager','proctor'], true)) {
        $student_code = trim((string)($_POST['student_id'] ?? ''));
        $first = trim((string)($_POST['first_name'] ?? ''));
        $middle = trim((string)($_POST['middle_name'] ?? ''));
        $last = trim((string)($_POST['last_name'] ?? ''));
        $sex = (string)($_POST['sex'] ?? '');
        $batch = trim((string)($_POST['batch'] ?? ''));
        $department = trim((string)($_POST['department'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        if ($student_code === '' || $first === '' || $last === '' || !in_array($sex, ['Male','Female'], true) || $department === '' || strlen($password) < 8) {
            $error = 'Complete the required student details and enter a login password with at least 8 characters.';
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO students (student_id, first_name, middle_name, last_name, sex, batch, department, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$student_code, $first, $middle ?: null, $last, $sex, $batch ?: null, $department, password_hash($password, PASSWORD_DEFAULT)]);
                flash('Student record and login created. Assign a room when ready.');
                redirect('students.php');
            } catch (PDOException $e) {
                $error = 'Could not save student. Student ID must be unique.';
            }
        }
    }
}
$students = $pdo->query('SELECT s.id, s.student_id, s.first_name, s.middle_name, s.last_name, s.sex, s.batch, s.department, (s.password_hash IS NOT NULL) AS has_login, r.room_number, b.block_number FROM students s LEFT JOIN rooms r ON r.id=s.room_id LEFT JOIN blocks b ON b.id=r.block_id ORDER BY s.created_at DESC')->fetchAll();
$unassigned = $pdo->query('SELECT id, student_id, first_name, middle_name, last_name, sex FROM students WHERE room_id IS NULL ORDER BY first_name, last_name')->fetchAll();
$rooms = $pdo->query('SELECT r.id, r.room_number, r.capacity, b.block_number, b.sex_category, (SELECT COUNT(*) FROM students s WHERE s.room_id=r.id) AS occupants FROM rooms r JOIN blocks b ON b.id=r.block_id ORDER BY b.block_number, r.room_number')->fetchAll();
$page_title = 'Manage Students';
require 'partials/header.php';
?>
<div class="page-heading"><div><p class="eyebrow">STUDENT RECORDS</p><h1>Manage students</h1><p class="muted">Register students and assign them to a compatible room.</p></div></div>
<?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
<div class="content-grid">
<section class="panel"><h2>Add student</h2><form method="post" class="form-stack">
<input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="create">
<label>Student ID<input name="student_id" required placeholder="e.g. BRU/CS/001"></label>
<div class="form-row"><label>First name<input name="first_name" required></label><label>Middle name<input name="middle_name"></label></div>
<label>Last name<input name="last_name" required></label>
<label>Sex<select name="sex" required><option value="">Choose...</option><option>Male</option><option>Female</option></select></label>
<label>Batch / year<input name="batch" placeholder="e.g. Year III"></label>
<label>Department<input name="department" required placeholder="e.g. Computer Science"></label>
<label>Student login password (minimum 8 characters)<input type="password" name="password" minlength="8" autocomplete="new-password" required></label>
<button class="button" type="submit">Save student</button>
</form></section>
<section class="panel"><h2>Assign a room</h2><form method="post" class="form-stack">
<input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="assign">
<label>Unassigned student<select name="student_pk" required><option value="">Choose student...</option><?php foreach ($unassigned as $s): ?><option value="<?= (int)$s['id'] ?>"><?= h($s['student_id'].' — '.$s['first_name'].' '.$s['last_name'].' ('.$s['sex'].')') ?></option><?php endforeach; ?></select></label>
<label>Available room<select name="room_id" required><option value="">Choose room...</option><?php foreach ($rooms as $r): if ((int)$r['occupants'] < (int)$r['capacity']): ?><option value="<?= (int)$r['id'] ?>"><?= h($r['block_number'].' / Room '.$r['room_number'].' — '.$r['sex_category'].' — '.((int)$r['capacity']-(int)$r['occupants']).' spaces left') ?></option><?php endif; endforeach; ?></select></label>
<button class="button" type="submit">Assign student</button>
<p class="help-text">The system checks room capacity and requires the student's sex to match the block category.</p>
</form></section>
</div>
<section class="panel section-top"><h2>All students</h2><div class="table-wrap"><table><thead><tr><th>Student ID</th><th>Full name</th><th>Sex</th><th>Batch</th><th>Department</th><th>Block</th><th>Room</th><th>Student login</th></tr></thead><tbody>
<?php foreach ($students as $s): ?><tr><td><?= h($s['student_id']) ?></td><td><?= h(trim($s['first_name'].' '.$s['middle_name'].' '.$s['last_name'])) ?></td><td><?= h($s['sex']) ?></td><td><?= h($s['batch'] ?? '') ?></td><td><?= h($s['department']) ?></td><td><?= h($s['block_number'] ?? 'Unassigned') ?></td><td><?= h($s['room_number'] ?? '—') ?></td><td><form method="post" class="form-stack"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="set_password"><input type="hidden" name="student_pk" value="<?= (int)$s['id'] ?>"><label><?= (int)$s['has_login'] === 1 ? 'Reset password' : 'Set password' ?><input type="password" name="password" minlength="8" autocomplete="new-password" required></label><button class="button" type="submit"><?= (int)$s['has_login'] === 1 ? 'Save password' : 'Enable login' ?></button></form></td></tr><?php endforeach; ?>
<?php if (!$students): ?><tr><td colspan="8" class="empty">No student records yet.</td></tr><?php endif; ?>
</tbody></table></div></section>
<?php require 'partials/footer.php'; ?>
