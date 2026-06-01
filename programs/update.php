<?php

require_once '../includes/auth.php';
require_login('../');
if (!can('Updater')) { header('Location: list.php'); exit; }
require_once '../config/db.php';

$base_path = '../';
$errors = [];
$id = $_GET['id'] ?? $_POST['prog_id'] ?? '';

// Fetch the program being edited
try {
    $stmt = $pdo->prepare("SELECT * FROM programs WHERE prog_id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    if (!$data) { header('Location: list.php?msg=Program+not+found.&type=error'); exit; }
} catch (PDOException $e) { die('Database error: ' . $e->getMessage()); }

// Resolve the locked department and school for display only
try {
    $deptStmt = $pdo->prepare("
        SELECT d.dept_id, d.dept_short_name, d.dept_full_name,
               s.school_short_name, s.school_full_name
        FROM departments d
        JOIN schools s ON s.school_id = d.school_id
        WHERE d.dept_id = ?
    ");
    $deptStmt->execute([$data['dept_id']]);
    $currentDept = $deptStmt->fetch();
    if (!$currentDept) { die('Error: Could not resolve department for this program.'); }
} catch (PDOException $e) { die('Database error: ' . $e->getMessage()); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['prog_full_name']  = trim($_POST['prog_full_name']  ?? '');
    $data['prog_short_name'] = trim($_POST['prog_short_name'] ?? '');

  

    if (empty($errors)) {
        try {
            // dept_id is NOT updated — locked to the original department at creation
            $stmt = $pdo->prepare("UPDATE programs SET prog_full_name=?, prog_short_name=? WHERE prog_id=?");
            $stmt->execute([$data['prog_full_name'], $data['prog_short_name'], $id]);
            header('Location: list.php?msg=Program+updated+successfully.&type=success');
            exit;
        } catch (PDOException $e) { $errors[] = 'Database error: ' . $e->getMessage(); }
    }
}

include '../includes/header.php';
?>

<p class="page-title">Program Update</p>

<?php if ($errors): ?>
    <div class="alert alert-error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="update.php">
        <input type="hidden" name="prog_id" value="<?= htmlspecialchars($data['prog_id']) ?>">

        <div class="form-row">
            <label>Program ID:</label>
            <input type="text" value="<?= htmlspecialchars($data['prog_id']) ?>" disabled
                   style="background:#f4f6f8;color:var(--muted);cursor:not-allowed;">
        </div>

        <div class="form-row">
            <label for="prog_full_name">Program Full Name:</label>
            <input type="text" id="prog_full_name" name="prog_full_name"
                   value="<?= htmlspecialchars($data['prog_full_name']) ?>"
                   maxlength="150"
                   pattern="[a-zA-Z\s\-'\.&]+"
                   title="Letters, spaces, hyphens, apostrophes, dots, and ampersands only — no numbers">
        </div>

        <div class="form-row">
            <label for="prog_short_name">Program Short Name:</label>
            <input type="text" id="prog_short_name" name="prog_short_name"
                   value="<?= htmlspecialchars($data['prog_short_name']) ?>"
                   maxlength="20"
                   pattern="[a-zA-Z]+"
                   title="Letters only — no numbers, spaces, or special characters">
        </div>

        <!-- Department is locked — program cannot be moved to a different department -->
        <div class="form-row">
            <label>Department:</label>
            <input type="text"
                   value="<?= htmlspecialchars($currentDept['dept_short_name'] . ' – ' . $currentDept['dept_full_name']) ?>"
                   disabled
                   style="background:#f4f6f8;color:var(--muted);cursor:not-allowed;">
        </div>

        <!-- School shown for context — also locked -->
        <div class="form-row">
            <label>School:</label>
            <input type="text"
                   value="<?= htmlspecialchars($currentDept['school_short_name'] . ' – ' . $currentDept['school_full_name']) ?>"
                   disabled
                   style="background:#f4f6f8;color:var(--muted);cursor:not-allowed;">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-gray">Save Changes</button>
            <button type="reset"  class="btn btn-outline">Reset</button>
            <a href="list.php"    class="btn btn-red">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>