<?php
require_once '../includes/auth.php';
require_login('../');
if (!can('Creator')) { header('Location: list.php'); exit; }
require_once '../config/db.php';

$base_path = '../';
$errors = [];
$data = ['dept_id' => '', 'dept_full_name' => '', 'dept_short_name' => '', 'school_id' => ''];

$schools = $pdo->query("SELECT school_id, school_short_name, school_full_name FROM schools ORDER BY school_id")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['dept_id']         = trim($_POST['dept_id']         ?? '');
    $data['dept_full_name']  = trim($_POST['dept_full_name']  ?? '');
    $data['dept_short_name'] = trim($_POST['dept_short_name'] ?? '');
    $data['school_id']       = trim($_POST['school_id']       ?? '');

    if ($data['dept_id'] === '' || !ctype_digit($data['dept_id']))
        $errors[] = 'Department ID must be a positive integer.';
    if ($data['dept_full_name'] === '')  $errors[] = 'Department Full Name is required.';
    if ($data['dept_short_name'] === '') $errors[] = 'Department Short Name is required.';
    if ($data['school_id'] === '')       $errors[] = 'Please select a School.';

    if (empty($errors)) {
        try {
            $chk = $pdo->prepare("SELECT dept_id FROM departments WHERE dept_id = ?");
            $chk->execute([$data['dept_id']]);
            if ($chk->fetch()) $errors[] = 'A department with that ID already exists.';
        } catch (PDOException $e) { $errors[] = 'Database error: ' . $e->getMessage(); }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO departments (dept_id, dept_full_name, dept_short_name, school_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$data['dept_id'], $data['dept_full_name'], $data['dept_short_name'], $data['school_id']]);
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

        <div class="form-row">
            <label for="dept_id">Department ID:</label>
            <input type="number" id="dept_id" name="dept_id"
                   value="<?= htmlspecialchars($data['dept_id']) ?>" min="1">
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
            <button type="submit" class="btn btn-gray">Save New Department</button>
            <button type="reset"  class="btn btn-outline">Reset Form</button>
            <a href="list.php"    class="btn btn-red">Exit</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>