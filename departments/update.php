<?php

require_once '../includes/auth.php';
require_login('../');
if (!can('Updater')) { header('Location: list.php'); exit; }
require_once '../config/db.php';

$base_path = '../';
$errors = [];
$id = $_GET['id'] ?? $_POST['dept_id'] ?? '';

$schools = $pdo->query("SELECT school_id, school_short_name, school_full_name FROM schools ORDER BY school_id")->fetchAll();

try {
    $stmt = $pdo->prepare("SELECT * FROM departments WHERE dept_id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    if (!$data) { header('Location: list.php?msg=Department+not+found.&type=error'); exit; }
} catch (PDOException $e) { die('Database error: ' . $e->getMessage()); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['dept_full_name']  = trim($_POST['dept_full_name']  ?? '');
    $data['dept_short_name'] = trim($_POST['dept_short_name'] ?? '');
    $data['school_id']       = trim($_POST['school_id']       ?? '');

    if ($data['dept_full_name'] === '')  $errors[] = 'Department Full Name is required.';
    if ($data['dept_short_name'] === '') $errors[] = 'Department Short Name is required.';
    if ($data['school_id'] === '')       $errors[] = 'Please select a School.';

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE departments SET dept_full_name=?, dept_short_name=?, school_id=? WHERE dept_id=?");
            $stmt->execute([$data['dept_full_name'], $data['dept_short_name'], $data['school_id'], $id]);
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

        <div class="form-row">
            <label for="school_id">School:</label>
            <select id="school_id" name="school_id">
                <option value="">-- Select School --</option>
                <?php foreach ($schools as $s): ?>
                    <option value="<?= $s['school_id'] ?>"
                        <?= $data['school_id'] == $s['school_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['school_short_name'] . ' – ' . $s['school_full_name']) ?>
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