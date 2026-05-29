<?php

require_once '../includes/auth.php';
require_login('../');
require_once '../config/db.php';

$base_path = '../';

$prog_id = $_GET['prog_id'] ?? '';
if ($prog_id === '') { header('Location: select.php'); exit; }
$msg  = $_GET['msg']  ?? '';
$type = $_GET['type'] ?? 'success';
$progStmt = $pdo->prepare("
    SELECT p.*, d.dept_short_name, s.school_short_name
    FROM programs p
    JOIN departments d ON d.dept_id = p.dept_id
    JOIN schools s ON s.school_id = d.school_id
    WHERE p.prog_id = ?
");
$progStmt->execute([$prog_id]);
$program = $progStmt->fetch();
if (!$program) { header('Location: select.php?msg=Program+not+found.&type=error'); exit; }

// Get students
$stmt = $pdo->prepare("
    SELECT * FROM students WHERE prog_id = ?
    ORDER BY student_last_name, student_first_name ASC
");
$stmt->execute([$prog_id]);
$students = $stmt->fetchAll();
$total = count($students);

$msg  = $_GET['msg']  ?? '';
$type = $_GET['type'] ?? 'success';

include '../includes/header.php';
?>

<p class="page-title">Student List</p>
<p class="page-subtitle">
    Program: <strong><?= htmlspecialchars($program['prog_short_name']) ?> – <?= htmlspecialchars($program['prog_full_name']) ?></strong><br>
    Department: <strong><?= htmlspecialchars($program['dept_short_name']) ?></strong> &nbsp;|&nbsp;
    School: <strong><?= htmlspecialchars($program['school_short_name']) ?></strong>
</p>

<?php if ($msg): ?>
    <div class="alert alert-<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="action-bar">
    <?php if (can('Creator')): ?>
        <a href="create.php?prog_id=<?= urlencode($prog_id) ?>" class="btn btn-green">+ Add Student</a>
    <?php endif; ?>
    <a href="select.php" class="btn btn-outline">← Change Program</a>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Student ID</th>
            <th>Last Name</th>
            <th>First Name</th>
            <th>Middle Name</th>
            <th>Year</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($students): ?>
        <?php foreach ($students as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['student_id']) ?></td>
            <td><?= htmlspecialchars($s['student_last_name']) ?></td>
            <td><?= htmlspecialchars($s['student_first_name']) ?></td>
            <td><?= htmlspecialchars($s['student_middle_name'] ?? '—') ?></td>
            <td><?= htmlspecialchars($s['student_year']) ?></td>
            <td style="display:flex;gap:6px;flex-wrap:wrap;">
                <?php if (can('Updater')): ?>
                    <a href="update.php?student_id=<?= urlencode($s['student_id']) ?>&prog_id=<?= urlencode($prog_id) ?>"
                       class="btn btn-green btn-sm">✎ Update</a>
                <?php endif; ?>
                <?php if (can('Remover')): ?>
                    <a href="delete.php?student_id=<?= urlencode($s['student_id']) ?>&prog_id=<?= urlencode($prog_id) ?>"
                       class="btn btn-red btn-sm"
                       onclick="return confirm('Delete this student record?');">🗑 Delete</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="6" style="text-align:center;color:var(--muted);">No students enrolled in this program.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<div class="table-footer">
    <span>Total of: <?= $total ?> student<?= $total !== 1 ? 's' : '' ?> enrolled</span>
</div>

<?php include '../includes/footer.php'; ?>  