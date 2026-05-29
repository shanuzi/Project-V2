<?php


require_once '../includes/auth.php';
require_login('../');
require_once '../config/db.php';

$base_path = '../';

$programs = $pdo->query("
    SELECT p.prog_id, p.prog_short_name, p.prog_full_name,
           d.dept_short_name, s.school_short_name
    FROM programs p
    JOIN departments d ON d.dept_id = p.dept_id
    JOIN schools s ON s.school_id = d.school_id
    ORDER BY s.school_id, d.dept_id, p.prog_id
")->fetchAll();

include '../includes/header.php';
?>

<p class="page-title">Students – Select Program</p>
<p class="page-subtitle">Choose a program to view and manage its enrolled students.</p>

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
        <tr><td colspan="6" style="text-align:center;color:var(--muted);">No programs found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>