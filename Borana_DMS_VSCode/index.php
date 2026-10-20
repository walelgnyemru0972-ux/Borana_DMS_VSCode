<?php
require_once 'config.php';
$page_title = 'Home | Borana University DMS';
require 'partials/header.php';
?>
<section class="hero">
  <div class="hero-copy">
    <p class="eyebrow">BORANA UNIVERSITY</p>
    <h1>Online Dormitory<br>Management System</h1>
    <p>Manage dormitory blocks, rooms, student records, and room assignments in one place.</p>
    <div class="hero-actions">
      <?php if (is_logged_in()): ?><a class="button" href="<?= current_role() === 'student' ? 'student_dorm.php' : 'dashboard.php' ?>"><?= current_role() === 'student' ? 'View my dorm' : 'Open dashboard' ?></a>
      <?php else: ?><a class="button" href="login.php">Sign in to continue</a><?php endif; ?>
      <a class="button button-light" href="#features">Explore features</a>
    </div>
  </div>
  <div class="hero-art" aria-hidden="true"><div class="building">⌂</div><div class="art-caption">Safe · Organized · Accountable</div></div>
</section>
<section id="features" class="section">
  <div class="section-heading"><p class="eyebrow">SYSTEM FEATURES</p><h2>Everything organized</h2></div>
  <div class="feature-grid">
    <article class="feature-card"><div class="feature-icon">▦</div><h3>Block management</h3><p>Register blocks and specify the sex category and faculty assigned to each block.</p></article>
    <article class="feature-card"><div class="feature-icon">⌂</div><h3>Room management</h3><p>Create rooms with a defined capacity and track available spaces.</p></article>
    <article class="feature-card"><div class="feature-icon">♙</div><h3>Student assignment</h3><p>Record students and assign them to rooms while enforcing room and block rules.</p></article>
    <article class="feature-card"><div class="feature-icon">▤</div><h3>Reports</h3><p>Review student assignments, room capacity, and dormitory records.</p></article>
  </div>
</section>
<?php require 'partials/footer.php'; ?>
