<?php
require_once 'includes/auth.php';
require_once 'config/db.php';

$base_path = '';

// Determine whether user is logged in
$logged_in = !empty($_SESSION['user_id']);
$is_admin  = $logged_in && $_SESSION['user_type'] === 'Administrator';

include 'includes/header.php';
?>

<?php if ($logged_in): ?>

<div class="welcome-banner">
    <h1>Welcome to USJ-R School Management System</h1>
    <p>Hello, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>! &#128075;</p>
    <p>Manage your school's operations efficiently</p>
</div>

<section class="quick-access">
    <h2>Quick Access</h2>
    <div class="qa-divider"></div>
    <div class="qa-grid">

        <!-- Schools -->
        <div class="qa-card">
            <div class="icon">&#127979;</div>
            <h3>Schools</h3>
            <p>Manage school information and details</p>
            <a href="schools/list.php" class="btn btn-green">View Schools</a>
        </div>

        <!-- Departments -->
        <div class="qa-card">
            <div class="icon">&#128218;</div>
            <h3>Departments</h3>
            <p>Organize departments within schools</p>
            <a href="departments/list.php" class="btn btn-green">View Departments</a>
        </div>

        <!-- Programs -->
        <div class="qa-card">
            <div class="icon">&#127891;</div>
            <h3>Programs</h3>
            <p>Manage academic programs and courses</p>
            <a href="programs/list.php" class="btn btn-green">View Programs</a>
        </div>

        <!-- Students -->
        <div class="qa-card">
            <div class="icon">&#128104;&#8205;&#127891;</div>
            <h3>Students</h3>
            <p>Manage student records and enrollment</p>
            <a href="students/select.php" class="btn btn-green">View Students</a>
        </div>

        <?php if ($is_admin): ?>
        <!-- User Management (admin only) -->
        <div class="qa-card highlight">
            <div class="icon">&#9881;&#65039;</div>
            <h3>User Management</h3>
            <p>Manage system users and permissions</p>
            <a href="users/list.php" class="btn btn-orange">Manage Users</a>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php else: ?>
<!-- ============================================================
     PUBLIC HOME  (not logged in)
     ============================================================ -->
<div class="welcome-banner">
    <h1>Welcome to USJ-R School Management System</h1>
    <p>Manage your school's operations efficiently</p>
</div>

<div class="qa-grid" style="margin-bottom:0;">
    <div class="qa-card"><div class="icon">&#127979;</div><h3>Schools</h3><p>Manage school information and details</p></div>
    <div class="qa-card"><div class="icon">&#128218;</div><h3>Departments</h3><p>Organize departments within schools</p></div>
    <div class="qa-card"><div class="icon">&#127891;</div><h3>Programs</h3><p>Manage academic programs and courses</p></div>
    <div class="qa-card"><div class="icon">&#128104;&#8205;&#127891;</div><h3>Students</h3><p>Manage student records and enrollment</p></div>
</div>

<div class="getting-started">
    <h2>Getting Started</h2>
    <ol>
        <li>Log in with your credentials</li>
        <li>Navigate to any section using the sidebar menu</li>
        <li>View, create, update, or delete records as needed</li>
        <li>Contact administrator for access requests</li>
    </ol>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>