<?php

require_once '../includes/auth.php';
require_login('../');
require_once '../config/db.php';

$base_path = '../';

$stmt = $pdo->query("
    SELECT d.*, s.school_short_name
    FROM departments d
    JOIN schools s ON s.school_id = d.school_id
    ORDER BY d.school_id, d.dept_id ASC
");
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
        <tr><td colspan="5" style="text-align:center;color:var(--muted);">No departments found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<div class="table-footer">
    <span>Total of: <?= $total ?> department<?= $total !== 1 ? 's' : '' ?> in the database</span>
</div>

<?php include '../includes/footer.php'; ?>