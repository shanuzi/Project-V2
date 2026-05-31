<?php

require_once '../includes/auth.php';
require_login('../');
if (!can('Updater')) { header('Location: list.php'); exit; }
require_once '../config/db.php';

$base_path = '../';
$errors = [];
$id = $_GET['id'] ?? $_POST['dept_id'] ?? '';

// Fetch the department being edited
try {
    $stmt = $pdo->prepare("SELECT * FROM departments WHERE dept_id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    if (!$data) { header('Location: list.php?msg=Department+not+found.&type=error'); exit; }
} catch (PDOException $e) { die('Database error: ' . $e->getMessage()); }

// Resolve the department's current school — this is locked and cannot be changed
try {
    $schoolStmt = $pdo->prepare("SELECT * FROM schools WHERE school_id = ?");
    $schoolStmt->execute([$data['school_id']]);
    $currentSchool = $schoolStmt->fetch();
    if (!$currentSchool) { die('Error: Could not resolve school for this department.'); }
} catch (PDOException $e) { die('Database error: ' . $e->getMessage()); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['dept_full_name']  = trim($_POST['dept_full_name']  ?? '');
    $data['dept_short_name'] = trim($_POST['dept_short_name'] ?? '');

    if ($data['dept_full_name'] === '')  $errors[] = 'Department Full Name is required.';
    if ($data['dept_short_name'] === '') $errors[] = 'Department Short Name is required.';

    if (empty($errors)) {
        try {
            // school_id is NOT updated — it stays as-is, locked to the original school
            $stmt = $pdo->prepare("UPDATE departments SET dept_full_name=?, dept_short_name=? WHERE dept_id=?");
            $stmt->execute([$data['dept_full_name'], $data['dept_short_name'], $id]);
            header('Location: list.php?msg=Department+updated+successfully.&type=success');
            exit;
        } catch (PDOException $e) { $errors[] = 'Database error: ' . $e->getMessage(); }
    }
}

include '../includes/header.php';
?>

<p class="page-title">Department Update</p>

<?php if ($errors): ?>
    <div class="alert alert-error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="update.php">
        <input type="hidden" name="dept_id" value="<?= htmlspecialchars($data['dept_id']) ?>">

        <div class="form-row">
            <label>Department ID:</label>
            <input type="text" value="<?= htmlspecialchars($data['dept_id']) ?>" disabled>
        </div>

        <div class="form-row">
            <label for="dept_full_name">Department Full Name:</label>
            <input type="text" id="dept_full_name" name="dept_full_name"
                   value="<?= htmlspecialchars($data['dept_full_name']) ?>" maxlength="150">
        </div>

        <div class="form-row">
            <label for="dept_short_name">Department Short Name:</label>
            <input type="text" id="dept_short_name" name="dept_short_name"
                   value="<?= htmlspecialchars($data['dept_short_name']) ?>" maxlength="20">
        </div>

        <!-- School is locked — a department cannot be moved to a different school -->
        <div class="form-row">
            <label>School:</label>
            <input type="text"
                   value="<?= htmlspecialchars($currentSchool['school_short_name'] . ' – ' . $currentSchool['school_full_name']) ?>"
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