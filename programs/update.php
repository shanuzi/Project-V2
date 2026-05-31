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

// Resolve the program's current school via its department
// This is the school the department dropdown will be locked to
try {
    $schoolStmt = $pdo->prepare("
        SELECT s.school_id, s.school_short_name, s.school_full_name
        FROM departments d
        JOIN schools s ON s.school_id = d.school_id
        WHERE d.dept_id = ?
    ");
    $schoolStmt->execute([$data['dept_id']]);
    $currentSchool = $schoolStmt->fetch();
    if (!$currentSchool) { die('Error: Could not resolve school for this program.'); }
} catch (PDOException $e) { die('Database error: ' . $e->getMessage()); }

// Fetch only the departments that belong to the same school
// Program can only move to a sibling department (same school)
try {
    $deptStmt = $pdo->prepare("
        SELECT dept_id, dept_short_name, dept_full_name
        FROM departments
        WHERE school_id = ?
        ORDER BY dept_id ASC
    ");
    $deptStmt->execute([$currentSchool['school_id']]);
    $departments = $deptStmt->fetchAll();
} catch (PDOException $e) { die('Database error: ' . $e->getMessage()); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['prog_full_name']  = trim($_POST['prog_full_name']  ?? '');
    $data['prog_short_name'] = trim($_POST['prog_short_name'] ?? '');
    $data['dept_id']         = trim($_POST['dept_id']         ?? '');

    if ($data['prog_full_name'] === '')  $errors[] = 'Program Full Name is required.';
    if ($data['prog_short_name'] === '') $errors[] = 'Program Short Name is required.';
    if ($data['dept_id'] === '')         $errors[] = 'Please select a Department.';

    // Security: verify submitted dept_id actually belongs to the same school
    if (!empty($data['dept_id'])) {
        $allowed_dept_ids = array_column($departments, 'dept_id');
        if (!in_array($data['dept_id'], $allowed_dept_ids)) {
            $errors[] = 'The selected department does not belong to this program\'s school.';
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE programs SET prog_full_name=?, prog_short_name=?, dept_id=? WHERE prog_id=?");
            $stmt->execute([$data['prog_full_name'], $data['prog_short_name'], $data['dept_id'], $id]);
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
            <input type="text" value="<?= htmlspecialchars($data['prog_id']) ?>" disabled>
        </div>

        <div class="form-row">
            <label for="prog_full_name">Program Full Name:</label>
            <input type="text" id="prog_full_name" name="prog_full_name"
                   value="<?= htmlspecialchars($data['prog_full_name']) ?>" maxlength="150">
        </div>

        <div class="form-row">
            <label for="prog_short_name">Program Short Name:</label>
            <input type="text" id="prog_short_name" name="prog_short_name"
                   value="<?= htmlspecialchars($data['prog_short_name']) ?>" maxlength="20">
        </div>

        <!-- School is locked — shown as read-only, not a free dropdown -->
        <div class="form-row">
            <label>School:</label>
            <input type="text"
                   value="<?= htmlspecialchars($currentSchool['school_short_name'] . ' – ' . $currentSchool['school_full_name']) ?>"
                   disabled
                   style="background:#f4f6f8;color:var(--muted);cursor:not-allowed;">
        </div>

        <!-- Department dropdown restricted to siblings in the same school -->
        <div class="form-row">
            <label for="dept_id">Department:</label>
            <select id="dept_id" name="dept_id">
                <option value="">-- Select Department --</option>
                <?php foreach ($departments as $d): ?>
                    <option value="<?= $d['dept_id'] ?>"
                        <?= $data['dept_id'] == $d['dept_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['dept_short_name'] . ' – ' . $d['dept_full_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-gray">Save Changes</button>
            <button type="reset"  class="btn btn-outline">Reset</button>
            <a href="list.php"    class="btn btn-red">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>