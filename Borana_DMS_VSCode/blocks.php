<?php
require_once 'config.php';
require_roles(['admin','manager']);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $block_number = trim((string)($_POST['block_number'] ?? ''));
    $sex_category = (string)($_POST['sex_category'] ?? '');
    $faculty = trim((string)($_POST['faculty'] ?? ''));
    if ($block_number === '' || $faculty === '' || !in_array($sex_category, ['Male','Female'], true)) {
        $error = 'Complete all fields and choose a valid sex category.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO blocks (block_number, sex_category, faculty) VALUES (?, ?, ?)');
            $stmt->execute([$block_number, $sex_category, $faculty]);
            flash('Block registered successfully.');
            redirect('blocks.php');
        } catch (PDOException $e) {
            $error = 'Could not register block. Make sure the block number is unique.';
        }
    }
}
$blocks = $pdo->query('SELECT b.*, COUNT(DISTINCT r.id) AS room_count FROM blocks b LEFT JOIN rooms r ON r.block_id=b.id GROUP BY b.id ORDER BY b.block_number')->fetchAll();
$page_title = 'Manage Blocks';
require 'partials/header.php';
?>
<div class="page-heading"><div><p class="eyebrow">ACCOMMODATION</p><h1>Manage blocks</h1><p class="muted">Register blocks and keep male and female accommodation separate.</p></div></div>
<?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
<div class="content-grid">
<section class="panel"><h2>Register a block</h2><form method="post" class="form-stack">
<input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
<label>Block number/name<input name="block_number" placeholder="e.g. Block A" required></label>
<label>Sex category<select name="sex_category" required><option value="">Choose...</option><option>Male</option><option>Female</option></select></label>
<label>Faculty / department group<input name="faculty" required placeholder="e.g. Engineering"></label>
<button class="button" type="submit">Save block</button></form></section>
<section class="panel"><h2>Registered blocks</h2><div class="table-wrap"><table><thead><tr><th>Block</th><th>Sex</th><th>Faculty</th><th>Rooms</th></tr></thead><tbody>
<?php foreach ($blocks as $b): ?><tr><td><?= h($b['block_number']) ?></td><td><?= h($b['sex_category']) ?></td><td><?= h($b['faculty']) ?></td><td><?= (int)$b['room_count'] ?></td></tr><?php endforeach; ?>
<?php if (!$blocks): ?><tr><td colspan="4" class="empty">No blocks registered.</td></tr><?php endif; ?>
</tbody></table></div></section>
</div>
<?php require 'partials/footer.php'; ?>
