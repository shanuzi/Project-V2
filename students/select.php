<?php

require_once '../includes/auth.php';
require_login('../');
require_once '../config/db.php';

$base_path = '../';


$filter_school = isset($_GET['school_id']) && $_GET['school_id'] !== '' ? (int)$_GET['school_id'] : 0;
$filter_dept   = isset($_GET['dept_id'])   && $_GET['dept_id']   !== '' ? (int)$_GET['dept_id']   : 0;

// Combobox data: all schools --------------------------------
$schools = $pdo->query("SELECT school_id, school_short_name, school_full_name FROM schools ORDER BY school_id ASC")->fetchAll();

// departments (filtered by school if chosen) -
if ($filter_school > 0) {
    $dStmt = $pdo->prepare("SELECT dept_id, dept_short_name, dept_full_name FROM departments WHERE school_id = ? ORDER BY dept_id ASC");
    $dStmt->execute([$filter_school]);
} else {
    $dStmt = $pdo->query("SELECT dept_id, dept_short_name, dept_full_name FROM departments ORDER BY dept_id ASC");
}
$dept_options = $dStmt->fetchAll();

$where  = [];
$params = [];

if ($filter_school > 0) { $where[] = 's.school_id = ?'; $params[] = $filter_school; }
if ($filter_dept   > 0) { $where[] = 'd.dept_id = ?';   $params[] = $filter_dept; }

$sql = "
    SELECT p.prog_id, p.prog_short_name, p.prog_full_name,
           d.dept_short_name, s.school_short_name
    FROM programs p
    JOIN departments d ON d.dept_id = p.dept_id
    JOIN schools    s ON s.school_id = d.school_id
    " . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . "
    ORDER BY s.school_id, d.dept_id, p.prog_id
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$programs = $stmt->fetchAll();

include '../includes/header.php';
?>

<p class="page-title">Students – Select Program</p>
<p class="page-subtitle">Choose a program to view and manage its enrolled students.</p>

<div class="action-bar">
    <form method="GET" action="select.php" class="filter-bar">
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
            <a href="select.php" class="btn btn-outline btn-sm">✕ Clear</a>
        <?php endif; ?>
    </form>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Program ID</th>
            <th>Program</th>
            <th>Short Name</th>
            <th>Department</th>
            <th>School</th>
            <th>Action</th>
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
            <td>
                <a href="list.php?prog_id=<?= $p['prog_id'] ?>" class="btn btn-green btn-sm">View Students</a>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="6" style="text-align:center;color:var(--muted);">
            No programs found<?= ($filter_school > 0 || $filter_dept > 0) ? ' for the selected filter' : '' ?>.
        </td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>