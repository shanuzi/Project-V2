<?php
$current = basename($_SERVER['PHP_SELF']);

// Build a simple helper to detect active page group
function is_active($keywords, $current) {
    foreach ($keywords as $k) {
        if (strpos($current, $k) !== false) return true;
    }
    return false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USJ-R School Management System</title>
    <link rel="stylesheet" href="<?= $base_path ?? '' ?>assets/style.css">
</head>
<body>


<header class="top-header">
    <span class="brand">USJ-R School Management System V1.01</span>
    <div class="user-area">
        <?php if (isset($_SESSION['user_name'])): ?>
            <span>You are logged in as: <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong></span>
            <span>&#128100;</span>
            <a href="<?= $base_path ?? '' ?>logout.php"
               class="btn btn-red btn-sm">Logout</a>
        <?php else: ?>
            <!-- Login form in header for public view -->
            <form method="POST" action="<?= $base_path ?? '' ?>login.php"
                  style="display:flex;align-items:center;gap:8px;">
                <label style="font-size:12px;">Username:</label>
                <input type="text" name="username" style="padding:4px 8px;font-size:13px;border-radius:4px;border:none;width:110px;">
                <label style="font-size:12px;">Password:</label>
                <input type="password" name="password" style="padding:4px 8px;font-size:13px;border-radius:4px;border:none;width:90px;">
                <button type="submit" class="btn btn-green btn-sm">Login</button>
            </form>
        <?php endif; ?>
    </div>
</header>

<!-- ========================================================
     SIDEBAR
     ======================================================== -->
<nav class="sidebar">
    <a href="<?= $base_path ?? '' ?>index.php"
       class="<?= is_active(['index'], $current) ? 'active' : '' ?>">Home</a>

    <a href="<?= $base_path ?? '' ?>schools/list.php"
       class="<?= is_active(['school'], $current) ? 'active' : '' ?>">Schools</a>

    <a href="<?= $base_path ?? '' ?>departments/list.php"
       class="<?= is_active(['department'], $current) ? 'active' : '' ?>">Departments</a>

    <a href="<?= $base_path ?? '' ?>programs/list.php"
       class="<?= is_active(['program'], $current) ? 'active' : '' ?>">Programs</a>

    <a href="<?= $base_path ?? '' ?>students/select.php"
       class="<?= is_active(['student'], $current) ? 'active' : '' ?>">Students</a>

    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Administrator'): ?>
    <a href="<?= $base_path ?? '' ?>users/list.php"
       class="<?= is_active(['user'], $current) ? 'active' : '' ?>">Users</a>
    <?php endif; ?>
</nav>

<!-- ========================================================
     MAIN CONTENT wrapper (opened here, closed in footer.php)
     ======================================================== -->
<main class="main-content">