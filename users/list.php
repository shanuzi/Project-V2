<?php

require_once '../includes/auth.php';
require_login('../');
require_once '../config/db.php';

$base_path = '../';

// ---- Fetch all users (PDO Version) -----------------------
$stmt = $pdo->query("SELECT * FROM users ORDER BY user_id ASC");
$users = $stmt->fetchAll();
$total = count($users);

// ---- Flash message from redirect ---------------------------
$msg  = $_GET['msg']  ?? '';
$type = $_GET['type'] ?? 'success';

include '../includes/header.php';
?>

<p class="page-title">User List</p>

<?php if ($msg): ?>
    <div class="alert alert-<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="action-bar">
    <?php if (can('Creator')): ?>
        <a href="create.php" class="btn btn-green">+ Create User Entry</a>
    <?php endif; ?>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>User ID</th>
            <th>Username</th>
            <th>User Type</th>
            <th>User Role</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($users): ?>
        <?php foreach ($users as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['user_id']) ?></td>
            <td><?= htmlspecialchars($s['user_name']) ?></td>
            <td><?= htmlspecialchars($s['user_type']) ?></td>
            <td><?= htmlspecialchars($s['user_role']) ?></td>
            <td style="display:flex;gap:6px;flex-wrap:wrap;">
                <?php if (can('Updater')): ?>
                    <a href="update.php?id=<?= $s['user_id'] ?>" class="btn btn-green btn-sm">✎ Update</a>
                <?php endif; ?>
                <?php if (can('Remover')): ?>
                    <a href="delete.php?id=<?= $s['user_id'] ?>" class="btn btn-red btn-sm"
                       onclick="return confirm('Delete this user? This action cannot be undone.');">
                       🗑 Delete</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="5" style="text-align:center;color:var(--muted);">No users found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<div class="table-footer">
    <span>Total of: <?= $total ?> user<?= $total !== 1 ? 's' : '' ?> in the database</span>
</div>

<?php include '../includes/footer.php'; ?>