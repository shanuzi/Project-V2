<?php
require_once 'includes/auth.php';
require_once 'config/db.php';

$base_path = '';

$logged_in = !empty($_SESSION['user_id']);
$is_admin  = $logged_in && $_SESSION['user_type'] === 'Administrator';

// --- Fetch entity counts (only when logged in) ---
$counts = ['schools' => 0, 'departments' => 0, 'programs' => 0, 'students' => 0, 'users' => 0];
if ($logged_in) {
    foreach (['schools', 'departments', 'programs', 'students', 'users'] as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $counts[$table] = (int) $stmt->fetchColumn();
    }
}

include 'includes/header.php';
?>

<?php if ($logged_in): ?>

<?php
$hour     = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$initials = strtoupper(substr($_SESSION['user_name'], 0, 2));
$today    = date('l, F j, Y');
?>

<style>
/* ── Welcome Banner ───────────────────────────────────────────── */
.wb-main {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 10px;
    display: flex;
    align-items: stretch;
    overflow: hidden;
    min-height: 150px;
    margin-bottom: 28px;
    box-shadow: var(--shadow);
}
.wb-accent {
    width: 5px;
    background: var(--green);
    flex-shrink: 0;
}
.wb-body {
    flex: 1;
    padding: 28px 28px 22px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 14px;
}
.wb-eyebrow {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .07em;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 5px;
}
.wb-heading {
    font-size: 22px;
    font-weight: 700;
    color: var(--text);
    margin: 0 0 5px;
    line-height: 1.3;
}
.wb-name { color: var(--green-dark); }
.wb-sub  { font-size: 13px; color: var(--muted); line-height: 1.6; margin: 0; }
.wb-tags { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.wb-tag  {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 12px; padding: 4px 10px;
    border-radius: var(--radius); border: 1px solid var(--border);
}
.wb-tag-muted { background: var(--gray-bg); color: var(--muted); }
.wb-tag-green { background: #e8f5e9; border-color: #a5d6a7; color: var(--green-dark); }
.wb-user {
    display: flex; flex-direction: column;
    justify-content: center; align-items: flex-end;
    gap: 14px; padding: 24px;
    border-left: 1px solid var(--border);
    min-width: 170px;
}
.wb-user-info  { text-align: right; }
.wb-user-label { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; margin: 0 0 2px; }
.wb-user-name  { font-size: 14px; font-weight: 700; color: var(--text); margin: 0; }
.wb-user-role  { font-size: 12px; color: var(--muted); margin: 2px 0 0; }
.wb-avatar {
    width: 42px; height: 42px; border-radius: 50%;
    background: #e8f5e9; border: 1px solid #a5d6a7;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 700; color: var(--green-dark);
}

/* ── Dashboard stat cards ─────────────────────────────────────── */
.stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.stat-card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 28px 22px 22px;
    text-align: center;
    box-shadow: var(--shadow);
    transition: transform .2s, box-shadow .2s;
    position: relative;
    overflow: hidden;
}
.stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: var(--card-accent, var(--green));
    border-radius: 10px 10px 0 0;
}
.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,.13);
}
.stat-card.highlight {
    border: 1px solid var(--orange);
}
.stat-card.highlight::before {
    background: var(--orange);
}

/* The big number */
.stat-number {
    font-size: 56px;
    font-weight: 700;
    line-height: 1;
    letter-spacing: -2px;
    color: var(--card-accent, var(--green));
    margin-bottom: 6px;
    font-variant-numeric: tabular-nums;
}

.stat-card h3 {
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: var(--text);
    margin-bottom: 6px;
}
.stat-card p {
    font-size: 12px;
    color: var(--muted);
    margin-bottom: 16px;
    line-height: 1.4;
}

/* color variants */
.stat-green  { --card-accent: #4caf50; }
.stat-blue   { --card-accent: #1976d2; }
.stat-teal   { --card-accent: #00897b; }
.stat-indigo { --card-accent: #5c6bc0; }
.stat-orange { --card-accent: #f5a623; }

/* count-up animation */
@keyframes countUp {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}
.stat-number { animation: countUp .45s ease both; }
.stat-card:nth-child(1) .stat-number { animation-delay: .05s; }
.stat-card:nth-child(2) .stat-number { animation-delay: .12s; }
.stat-card:nth-child(3) .stat-number { animation-delay: .19s; }
.stat-card:nth-child(4) .stat-number { animation-delay: .26s; }
.stat-card:nth-child(5) .stat-number { animation-delay: .33s; }
</style>

<div class="wb-main">
    <div class="wb-accent"></div>
    <div class="wb-body">
        <div>
            <p class="wb-eyebrow">USJ-R School Management System</p>
            <h1 class="wb-heading"><?= $greeting ?>, <span class="wb-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span> &#128075;</h1>
            <p class="wb-sub">Manage your school's operations efficiently from the dashboard below.</p>
        </div>
        <div class="wb-tags">
            <span class="wb-tag wb-tag-muted">&#128197; <?= $today ?></span>
            <span class="wb-tag wb-tag-green">&#10003; System operational</span>
        </div>
    </div>
    <div class="wb-user">
        <div class="wb-user-info">
            <p class="wb-user-label">Logged in as</p>
            <p class="wb-user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></p>
            <p class="wb-user-role"><?= htmlspecialchars($_SESSION['user_role']) ?></p>
        </div>
        <div class="wb-avatar"><?= $initials ?></div>
    </div>
</div>

<section class="quick-access">
    <h2>Quick Access</h2>
    <div class="qa-divider"></div>
    <div class="stat-grid">

        <!-- Schools -->
        <div class="stat-card stat-green">
            <div class="stat-number"><?= $counts['schools'] ?></div>
            <h3>Schools</h3>
            <p>Manage school information and details</p>
            <a href="schools/list.php" class="btn btn-green">View Schools</a>
        </div>

        <!-- Departments -->
        <div class="stat-card stat-blue">
            <div class="stat-number"><?= $counts['departments'] ?></div>
            <h3>Departments</h3>
            <p>Organize departments within schools</p>
            <a href="departments/list.php" class="btn btn-green">View Departments</a>
        </div>

        <!-- Programs -->
        <div class="stat-card stat-teal">
            <div class="stat-number"><?= $counts['programs'] ?></div>
            <h3>Programs</h3>
            <p>Manage academic programs and courses</p>
            <a href="programs/list.php" class="btn btn-green">View Programs</a>
        </div>

        <!-- Students -->
        <div class="stat-card stat-indigo">
            <div class="stat-number"><?= $counts['students'] ?></div>
            <h3>Students</h3>
            <p>Manage student records and enrollment</p>
            <a href="students/select.php" class="btn btn-green">View Students</a>
        </div>

        <?php if ($is_admin): ?>
        <!-- Users (admin only) -->
        <div class="stat-card stat-orange highlight">
            <div class="stat-number"><?= $counts['users'] ?></div>
            <h3>Users</h3>
            <p>Manage system users and permissions</p>
            <a href="users/list.php" class="btn btn-orange">Manage Users</a>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php else: ?>
<!-- ── PUBLIC HOME (not logged in) ─────────────────────────────── -->
<style>
.stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}
.stat-card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 28px 22px 22px;
    text-align: center;
    box-shadow: var(--shadow);
    position: relative;
    overflow: hidden;
}
.stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: var(--card-accent, var(--green));
    border-radius: 10px 10px 0 0;
}
.stat-number {
    font-size: 56px;
    font-weight: 700;
    line-height: 1;
    letter-spacing: -2px;
    color: var(--card-accent, var(--green));
    margin-bottom: 6px;
}
.stat-card h3 {
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    margin-bottom: 6px;
}
.stat-card p { font-size: 12px; color: var(--muted); line-height: 1.4; }
.stat-green  { --card-accent: #4caf50; }
.stat-blue   { --card-accent: #1976d2; }
.stat-teal   { --card-accent: #00897b; }
.stat-indigo { --card-accent: #5c6bc0; }
</style>

<div class="welcome-banner">
    <h1>Welcome to USJ-R School Management System</h1>
    <p>Manage your school's operations efficiently</p>
</div>

<div class="stat-grid" style="margin-bottom:0;">
    <div class="stat-card stat-green">
        <div class="stat-number">—</div>
        <h3>Schools</h3>
        <p>Manage school information and details</p>
    </div>
    <div class="stat-card stat-blue">
        <div class="stat-number">—</div>
        <h3>Departments</h3>
        <p>Organize departments within schools</p>
    </div>
    <div class="stat-card stat-teal">
        <div class="stat-number">—</div>
        <h3>Programs</h3>
        <p>Manage academic programs and courses</p>
    </div>
    <div class="stat-card stat-indigo">
        <div class="stat-number">—</div>
        <h3>Students</h3>
        <p>Manage student records and enrollment</p>
    </div>
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