<?php

require_once '../includes/auth.php';
require_login('../');
if (!can('Updater')) { header('Location: list.php'); exit; }
require_once '../config/db.php';

$base_path = '../';
$errors = [];

$id = $_GET['id'] ?? $_POST['school_id'] ?? '';

try {
    $stmt = $pdo->prepare("SELECT * FROM schools WHERE school_id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    if (!$data) {
        header('Location: list.php?msg=School+not+found.&type=error');
        exit;
    }
} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['school_full_name']  = trim($_POST['school_full_name']  ?? '');
    $data['school_short_name'] = trim($_POST['school_short_name'] ?? '');

    if ($data['school_full_name'] === '')  $errors[] = 'School Full Name is required.';
    if ($data['school_short_name'] === '') $errors[] = 'School Short Name is required.';

    if (empty($errors)) {
        try {
            $sql  = "UPDATE schools SET school_full_name = ?, school_short_name = ? WHERE school_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['school_full_name'], $data['school_short_name'], $id]);
            header('Location: list.php?msg=School+updated+successfully.&type=success');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<p class="page-title">School Update</p>

<?php if ($errors): ?>
    <div class="alert alert-error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="update.php">
        <input type="hidden" name="school_id" value="<?= htmlspecialchars($data['school_id']) ?>">

        <div class="form-row">
            <label>School ID:</label>
            <input type="text" value="<?= htmlspecialchars($data['school_id']) ?>" disabled>
        </div>

        <div class="form-row">
            <label for="school_full_name">School Full Name:</label>
            <input type="text" id="school_full_name" name="school_full_name"
                   value="<?= htmlspecialchars($data['school_full_name']) ?>" maxlength="150">
        </div>

        <div class="form-row">
            <label for="school_short_name">School Short Name:</label>
            <input type="text" id="school_short_name" name="school_short_name"
                   value="<?= htmlspecialchars($data['school_short_name']) ?>" maxlength="20">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-gray">Save Changes</button>
            <button type="reset"  class="btn btn-outline">Reset</button>
            <a href="list.php"    class="btn btn-red">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>