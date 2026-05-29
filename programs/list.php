<?php

require_once '../includes/auth.php';
require_login('../');
require_once '../config/db.php';

$base_path = '../';

// ---- Filters from GET ------------------------------------------
$filter_school = isset($_GET['school_id']) && $_GET['school_id'] !== '' ? (int)$_GET['school_id'] : 0;
$filter_dept   = isset($_GET['dept_id'])   && $_GET['dept_id']   !== '' ? (int)$_GET['dept_id']   : 0;

// ---- Combobox data: all schools --------------------------------
$schools = $pdo->query("SELECT school_id, school_short_name, school_full_name FROM schools ORDER BY school_id ASC")->fetchAll();

if ($filter_school > 0) {
    $dStmt = $pdo->prepare("SELECT dept_id, dept_short_name, dept_full_name FROM departments WHERE school_id = ? ORDER BY dept_id ASC");
    $dStmt->execute([$filter_school]);
} else {
    $dStmt = $pdo->query("SELECT dept_id, dept_short_name, dept_full_name FROM departments ORDER BY dept_id ASC");
}
$dept_options = $dStmt->fetchAll();

// ---- Main query ------------------------------------------------
$where  = [];
$params = [];

if ($filter_school > 0) { $where[] = 's.school_id = ?'; $params[] = $filter_school; }
if ($filter_dept   > 0) { $where[] = 'p.dept_id = ?';   $params[] = $filter_dept; }

$sql = "
    SELECT p.*, d.dept_short_name, s.school_short_name
    FROM programs p
    JOIN departments d ON d.dept_id = p.dept_id
    JOIN schools    s ON s.school_id = d.school_id
    " . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . "
    ORDER BY p.dept_id, p.prog_id ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$programs = $stmt->fetchAll();
$total = count($programs);

$msg  = $_GET['msg']  ?? '';
$type = $_GET['type'] ?? 'success';

include '../includes/header.php';
?>

<p class="page-title">Program List</p>

<?php if ($msg): ?>
    <div class="alert alert-<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="action-bar">
    <?php if (can('Creator')): ?>
        <a href="create.php" class="btn btn-green">+ Create Program Entry</a>
    <?php endif; ?>

    <!-- Filter bar -->
    <form method="GET" action="list.php" class="filter-bar">
        <label for="school_id">School:</label>
        <select id="school_id" name="school_id" onchange="this.form.submit()">
            <option value="">— All Schools —</option>
            <?php foreach ($schools as $s): ?>
                <option value="<?= $s['school_id'] ?>"
                    <?= $filter_school === (int)$s['school_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['school_short_name'] . ' – ' . $s['school_full_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="dept_id">Department:</label>
        <select id="dept_id" name="dept_id" onchange="this.form.submit()">
            <option value="">— All Departments —</option>
            <?php foreach ($dept_options as $d): ?>
                <option value="<?= $d['dept_id'] ?>"
                    <?= $filter_dept === (int)$d['dept_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($d['dept_short_name'] . ' – ' . $d['dept_full_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if ($filter_school > 0 || $filter_dept > 0): ?>
            <a href="list.php" class="btn btn-outline btn-sm">✕ Clear</a>
        <?php endif; ?>
    </form>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Program ID</th>
            <th>Full Name</th>
            <th>Short Name</th>
            <th>Department</th>
            <th>School</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($programs): ?>
        <?php foreach ($programs as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['prog_id']) ?></td>
            <td><?= htmlspecialchars($p['prog_full_name']) ?></td>
            <td><?= htmlspecialchars($p['prog_short_name']) ?></td>
            <td><?= htmlspecialchars($p['dept_short_name']) ?></td>
            <td><?= htmlspecialchars($p['school_short_name']) ?></td>
            <td style="display:flex;gap:6px;flex-wrap:wrap;">
                <?php if (can('Updater')): ?>
                    <a href="update.php?id=<?= $p['prog_id'] ?>" class="btn btn-green btn-sm">✎ Update</a>
                <?php endif; ?>
                <?php if (can('Remover')): ?>
                    <a href="delete.php?id=<?= $p['prog_id'] ?>" class="btn btn-red btn-sm"
                       onclick="return confirm('Delete this program? This will fail if students are enrolled.');">
                       🗑 Delete</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="6" style="text-align:center;color:var(--muted);">No programs found<?= ($filter_school > 0 || $filter_dept > 0) ? ' for the selected filter' : '' ?>.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<div class="table-footer">
    <span>Total of: <?= $total ?> program<?= $total !== 1 ? 's' : '' ?>
        <?= ($filter_school > 0 || $filter_dept > 0) ? ' matching filter' : ' in the database' ?></span>
</div>

<?php include '../includes/footer.php'; ?>