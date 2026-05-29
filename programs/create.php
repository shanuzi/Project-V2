<?php

require_once '../includes/auth.php';
require_login('../');
if (!can('Creator')) { header('Location: list.php'); exit; }
require_once '../config/db.php';

$base_path = '../';
$errors = [];
$data = ['prog_id' => '', 'prog_full_name' => '', 'prog_short_name' => '', 'dept_id' => ''];

$departments = $pdo->query("
    SELECT d.dept_id, d.dept_short_name, d.dept_full_name, s.school_short_name
    FROM departments d JOIN schools s ON s.school_id = d.school_id
    ORDER BY s.school_id, d.dept_id
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['prog_id']         = trim($_POST['prog_id']         ?? '');
    $data['prog_full_name']  = trim($_POST['prog_full_name']  ?? '');
    $data['prog_short_name'] = trim($_POST['prog_short_name'] ?? '');
    $data['dept_id']         = trim($_POST['dept_id']         ?? '');

    if ($data['prog_id'] === '' || !ctype_digit($data['prog_id']))
        $errors[] = 'Program ID must be a positive integer.';
    if ($data['prog_full_name'] === '')  $errors[] = 'Program Full Name is required.';
    if ($data['prog_short_name'] === '') $errors[] = 'Program Short Name is required.';
    if ($data['dept_id'] === '')         $errors[] = 'Please select a Department.';

    if (empty($errors)) {
        $chk = $pdo->prepare("SELECT prog_id FROM programs WHERE prog_id = ?");
        $chk->execute([$data['prog_id']]);
        if ($chk->fetch()) $errors[] = 'A program with that ID already exists.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO programs (prog_id, prog_full_name, prog_short_name, dept_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$data['prog_id'], $data['prog_full_name'], $data['prog_short_name'], $data['dept_id']]);
            header('Location: list.php?msg=Program+added+successfully.&type=success');
            exit;
        } catch (PDOException $e) { $errors[] = 'Database error: ' . $e->getMessage(); }
    }
}

include '../includes/header.php';
?>

<p class="page-title">Program Create</p>

<?php if ($errors): ?>
    <div class="alert alert-error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="create.php">

        <div class="form-row">
            <label for="prog_id">Program ID:</label>
            <input type="number" id="prog_id" name="prog_id"
                   value="<?= htmlspecialchars($data['prog_id']) ?>" min="1">
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

        <div class="form-row">
            <label for="dept_id">Department:</label>
            <select id="dept_id" name="dept_id">
                <option value="">-- Select Department --</option>
                <?php foreach ($departments as $d): ?>
                    <option value="<?= $d['dept_id'] ?>"
                        <?= $data['dept_id'] == $d['dept_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars('[' . $d['school_short_name'] . '] ' . $d['dept_short_name'] . ' – ' . $d['dept_full_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-gray">Save New Program</button>
            <button type="reset"  class="btn btn-outline">Reset Form</button>
            <a href="list.php"    class="btn btn-red">Exit</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>