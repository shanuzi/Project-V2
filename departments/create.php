<?php
require_once '../includes/auth.php';
require_login('../');
if (!can('Creator')) { header('Location: list.php'); exit; }
require_once '../config/db.php';

$base_path = '../';
$errors = [];
$data = ['dept_full_name' => '', 'dept_short_name' => '', 'school_id' => ''];

$schools = $pdo->query("SELECT school_id, school_short_name, school_full_name FROM schools ORDER BY school_id")->fetchAll();

// --- Generate preview dept_id based on selected school ---

$preview_id = null;
$selected_school = $_POST['school_id'] ?? $_GET['school_id'] ?? '';
if ($selected_school !== '') {
    $cntStmt = $pdo->prepare("SELECT COUNT(*) FROM departments WHERE school_id = ?");
    $cntStmt->execute([$selected_school]);
    $cnt = (int) $cntStmt->fetchColumn();
    $preview_id = $selected_school . str_pad($cnt + 1, 3, '0', STR_PAD_LEFT);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['dept_full_name']  = trim($_POST['dept_full_name']  ?? '');
    $data['dept_short_name'] = trim($_POST['dept_short_name'] ?? '');
    $data['school_id']       = trim($_POST['school_id']       ?? '');

    if ($data['dept_full_name'] === '')  $errors[] = 'Department Full Name is required.';
    if ($data['dept_short_name'] === '') $errors[] = 'Department Short Name is required.';
    if ($data['school_id'] === '')       $errors[] = 'Please select a School.';

    if (empty($errors)) {
        // Re-compute the ID at save time to avoid race conditions
        $cntStmt = $pdo->prepare("SELECT COUNT(*) FROM departments WHERE school_id = ?");
        $cntStmt->execute([$data['school_id']]);
        $cnt = (int) $cntStmt->fetchColumn();
        $dept_id = (int) ($data['school_id'] . str_pad($cnt + 1, 3, '0', STR_PAD_LEFT));

        try {
            $chk = $pdo->prepare("SELECT dept_id FROM departments WHERE dept_id = ?");
            $chk->execute([$dept_id]);
            if ($chk->fetch()) $errors[] = 'Generated Department ID already exists. Please try again.';
        } catch (PDOException $e) { $errors[] = 'Database error: ' . $e->getMessage(); }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO departments (dept_id, dept_full_name, dept_short_name, school_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$dept_id, $data['dept_full_name'], $data['dept_short_name'], $data['school_id']]);
            header('Location: list.php?msg=Department+added+successfully.&type=success');
            exit;
        } catch (PDOException $e) { $errors[] = 'Database error: ' . $e->getMessage(); }
    }
}

include '../includes/header.php';
?>

<p class="page-title">Department Create</p>

<?php if ($errors): ?>
    <div class="alert alert-error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="create.php">

        <!-- School must be selected first so the ID preview can generate -->
        <div class="form-row">
            <label for="school_id">School:</label>
            <select id="school_id" name="school_id" onchange="this.form.submit()">
                <option value="">-- Select School --</option>
                <?php foreach ($schools as $s): ?>
                    <option value="<?= $s['school_id'] ?>"
                        <?= $data['school_id'] == $s['school_id'] || $selected_school == $s['school_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['school_short_name'] . ' – ' . $s['school_full_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Auto-generated ID — read-only display -->
        <div class="form-row">
            <label>Department ID:</label>
            <?php if ($preview_id !== null): ?>
                <input type="text" value="<?= htmlspecialchars($preview_id) ?>" disabled
                       style="background:#f4f6f8;color:var(--muted);cursor:not-allowed;font-weight:700;">
                <span class="field-error" style="color:var(--muted);font-size:11px;">Auto-generated</span>
            <?php else: ?>
                <input type="text" value="— Select a school first —" disabled
                       style="background:#f4f6f8;color:var(--muted);cursor:not-allowed;">
            <?php endif; ?>
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

        <div class="form-actions">
            <button type="submit" class="btn btn-gray">Save New Department</button>
            <button type="reset"  class="btn btn-outline">Reset Form</button>
            <a href="list.php"    class="btn btn-red">Exit</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>