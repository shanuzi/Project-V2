<?php

require_once '../includes/auth.php';
require_login('../');
require_once '../config/db.php';

$base_path = '../';

$stmt = $pdo->query("
    SELECT p.*, d.dept_short_name, s.school_short_name
    FROM programs p
    JOIN departments d ON d.dept_id = p.dept_id
    JOIN schools s ON s.school_id = d.school_id
    ORDER BY p.dept_id, p.prog_id ASC
");
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
        <tr><td colspan="6" style="text-align:center;color:var(--muted);">No programs found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<div class="table-footer">
    <span>Total of: <?= $total ?> program<?= $total !== 1 ? 's' : '' ?> in the database</span>
</div>

<?php include '../includes/footer.php'; ?>