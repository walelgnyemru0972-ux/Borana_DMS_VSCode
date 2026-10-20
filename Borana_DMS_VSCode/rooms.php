<?php
require_once 'config.php';
require_roles(['admin','manager','proctor']);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array(current_role(), ['admin','manager'], true)) {
    verify_csrf();
    $block_id = filter_input(INPUT_POST, 'block_id', FILTER_VALIDATE_INT);
    $room_number = trim((string)($_POST['room_number'] ?? ''));
    $capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT);
    if (!$block_id || $room_number === '' || !$capacity || $capacity < 1 || $capacity > 6) {
        $error = 'Choose a block, enter a room number, and set capacity from 1 to 6.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO rooms (block_id, room_number, capacity) VALUES (?, ?, ?)');
            $stmt->execute([$block_id, $room_number, $capacity]);
            flash('Room registered successfully.');
            redirect('rooms.php');
        } catch (PDOException $e) {
            $error = 'Could not register room. The room number must be unique within its block.';
        }
    }
}
$blocks = $pdo->query('SELECT id, block_number, sex_category FROM blocks ORDER BY block_number')->fetchAll();
$rooms = $pdo->query('SELECT r.*, b.block_number, b.sex_category, COUNT(s.id) AS occupants FROM rooms r JOIN blocks b ON b.id=r.block_id LEFT JOIN students s ON s.room_id=r.id GROUP BY r.id ORDER BY b.block_number, r.room_number')->fetchAll();
$page_title = 'Manage Rooms';
require 'partials/header.php';
?>
<div class="page-heading"><div><p class="eyebrow">ACCOMMODATION</p><h1>Manage rooms</h1><p class="muted">Each room supports up to six students, depending on its configured capacity.</p></div></div>
<?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
<?php if (in_array(current_role(), ['admin','manager'], true)): ?>
<div class="panel form-panel"><h2>Register a room</h2><form method="post" class="form-row">
<input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
<label>Block<select name="block_id" required><option value="">Choose block...</option><?php foreach ($blocks as $b): ?><option value="<?= (int)$b['id'] ?>"><?= h($b['block_number'].' — '.$b['sex_category']) ?></option><?php endforeach; ?></select></label>
<label>Room number<input name="room_number" required placeholder="e.g. ROOM 101"></label>
<label>Capacity<select name="capacity" required><option value="6">6 students</option><option value="5">5 students</option><option value="4">4 students</option><option value="3">3 students</option><option value="2">2 students</option><option value="1">1 student</option></select></label>
<button class="button" type="submit">Save room</button>
</form></div>
<?php endif; ?>
<section class="panel"><h2>Rooms and occupancy</h2><div class="table-wrap"><table><thead><tr><th>Block</th><th>Room</th><th>Sex category</th><th>Capacity</th><th>Occupants</th><th>Available</th></tr></thead><tbody>
<?php foreach ($rooms as $r): ?><tr><td><?= h($r['block_number']) ?></td><td><?= h($r['room_number']) ?></td><td><?= h($r['sex_category']) ?></td><td><?= (int)$r['capacity'] ?></td><td><?= (int)$r['occupants'] ?></td><td><?= max(0,(int)$r['capacity']-(int)$r['occupants']) ?></td></tr><?php endforeach; ?>
<?php if (!$rooms): ?><tr><td colspan="6" class="empty">No rooms registered yet.</td></tr><?php endif; ?>
</tbody></table></div></section>
<?php require 'partials/footer.php'; ?>
