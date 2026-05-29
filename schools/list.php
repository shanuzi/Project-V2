<?php

require_once '../includes/auth.php';
require_login('../');
require_once '../config/db.php';

$base_path = '../';

// Fetch all schools 
$stmt = $pdo->query("SELECT * FROM schools ORDER BY school_id ASC");
$schools = $stmt->fetchAll();
$total = count($schools);

// ---- Flash message from redirect ---------------------------
$msg  = $_GET['msg']  ?? '';
$type = $_GET['type'] ?? 'success';

include '../includes/header.php';
?>

<p class="page-title">School List</p>

<?php if ($msg): ?>
    <div class="alert alert-<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="action-bar">
    <?php if (can('Creator')): ?>
        <a href="create.php" class="btn btn-green">+ Create School Entry</a>
    <?php endif; ?>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>School ID</th>
            <th>School Full Name</th>
            <th>School Short Name</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($schools): ?>
        <?php foreach ($schools as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['school_id']) ?></td>
            <td><?= htmlspecialchars($s['school_full_name']) ?></td>
            <td><?= htmlspecialchars($s['school_short_name']) ?></td>
            <td style="display:flex;gap:6px;flex-wrap:wrap;">
                <?php if (can('Updater')): ?>
                    <a href="update.php?id=<?= $s['school_id'] ?>" class="btn btn-green btn-sm">✎ Update</a>
                <?php endif; ?>
                <?php if (can('Remover')): ?>
                    <a href="delete.php?id=<?= $s['school_id'] ?>" class="btn btn-red btn-sm"
                       onclick="return confirm('Delete this school? This will fail if it has departments.');">
                       🗑 Delete</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="4" style="text-align:center;color:var(--muted);">No schools found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<div class="table-footer">
    <span>Total of: <?= $total ?> school<?= $total !== 1 ? 's' : '' ?> in the database</span>
</div>

<?php include '../includes/footer.php'; ?>
