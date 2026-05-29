<?php

require_once '../includes/auth.php';
require_login('../');
require_once '../config/db.php';

$base_path = '../';

// ---- Filter: school_id from GET --------------------------------
$filter_school = isset($_GET['school_id']) && $_GET['school_id'] !== '' ? (int)$_GET['school_id'] : 0;

// ---- Fetch all schools for the combobox ------------------------
$schools = $pdo->query("SELECT school_id, school_short_name, school_full_name FROM schools ORDER BY school_id ASC")->fetchAll();

// ---- Fetch departments (filtered or all) -----------------------
if ($filter_school > 0) {
    $stmt = $pdo->prepare("
        SELECT d.*, s.school_short_name
        FROM departments d
        JOIN schools s ON s.school_id = d.school_id
        WHERE d.school_id = ?
        ORDER BY d.dept_id ASC
    ");
    $stmt->execute([$filter_school]);
} else {
    $stmt = $pdo->query("
        SELECT d.*, s.school_short_name
        FROM departments d
        JOIN schools s ON s.school_id = d.school_id
        ORDER BY d.school_id, d.dept_id ASC
    ");
}
$departments = $stmt->fetchAll();
$total = count($departments);

$msg  = $_GET['msg']  ?? '';
$type = $_GET['type'] ?? 'success';

include '../includes/header.php';
?>

<p class="page-title">Department List</p>

<?php if ($msg): ?>
    <div class="alert alert-<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="action-bar">
    <?php if (can('Creator')): ?>
        <a href="create.php" class="btn btn-green">+ Create Department Entry</a>
    <?php endif; ?>

    <!-- Filter bar -->
    <form method="GET" action="list.php" class="filter-bar">
        <label for="school_id">Filter by School:</label>
        <select id="school_id" name="school_id" onchange="this.form.submit()">
            <option value="">— All Schools —</option>
            <?php foreach ($schools as $s): ?>
                <option value="<?= $s['school_id'] ?>"
                    <?= $filter_school === (int)$s['school_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['school_short_name'] . ' – ' . $s['school_full_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($filter_school > 0): ?>
            <a href="list.php" class="btn btn-outline btn-sm">✕ Clear</a>
        <?php endif; ?>
    </form>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Dept ID</th>
            <th>Full Name</th>
            <th>Short Name</th>
            <th>School</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($departments): ?>
        <?php foreach ($departments as $d): ?>
        <tr>
            <td><?= htmlspecialchars($d['dept_id']) ?></td>
            <td><?= htmlspecialchars($d['dept_full_name']) ?></td>
            <td><?= htmlspecialchars($d['dept_short_name']) ?></td>
            <td><?= htmlspecialchars($d['school_short_name']) ?></td>
            <td style="display:flex;gap:6px;flex-wrap:wrap;">
                <?php if (can('Updater')): ?>
                    <a href="update.php?id=<?= $d['dept_id'] ?>" class="btn btn-green btn-sm">✎ Update</a>
                <?php endif; ?>
                <?php if (can('Remover')): ?>
                    <a href="delete.php?id=<?= $d['dept_id'] ?>" class="btn btn-red btn-sm"
                       onclick="return confirm('Delete this department? This will fail if it has programs.');">
                       🗑 Delete</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="5" style="text-align:center;color:var(--muted);">No departments found<?= $filter_school > 0 ? ' for the selected school' : '' ?>.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<div class="table-footer">
    <span>Total of: <?= $total ?> department<?= $total !== 1 ? 's' : '' ?>
        <?= $filter_school > 0 ? ' in selected school' : ' in the database' ?></span>
</div>

<?php include '../includes/footer.php'; ?>