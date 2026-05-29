<?php
require_once '../includes/auth.php';
require_login('../');
if (!can('Creator')) {
    header('Location: list.php');
    exit;
}

require_once '../config/db.php'; 
$base_path = '../';

$errors = [];


$school_id = '';
$school_full_name = '';
$school_short_name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $school_id        = trim($_POST['school_id'] ?? '');
    $school_full_name = trim($_POST['school_full_name'] ?? '');
    $school_short_name = trim($_POST['school_short_name'] ?? '');

    /* Validation */
    if ($school_id === '' || !ctype_digit($school_id)) {
        $errors[] = 'School ID must be a positive integer.';
    }
    if ($school_full_name === '') {
        $errors[] = 'School Full Name is required.';
    }
    if ($school_short_name === '') {
        $errors[] = 'School Short Name is required.';
    }

    /* Check duplicate ID using PDO */
    if (empty($errors)) {
        try {
            $chk = $pdo->prepare("SELECT school_id FROM schools WHERE school_id = ?");
            $chk->execute([$school_id]);
            if ($chk->fetch()) {
                $errors[] = 'A school with that ID already exists.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }

   // insert
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO schools (school_id, school_full_name, school_short_name) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$school_id, $school_full_name, $school_short_name])) {
                header('Location: list.php?msg=School+added+successfully.&type=success');
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<p class="page-title">School Create</p>

<?php if ($errors): ?>
    <div class="alert alert-error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="create.php">
        
        <div class="form-row">
            <label for="school_id">School ID:</label>
            <input type="number" id="school_id" name="school_id" value="<?= htmlspecialchars($school_id) ?>" min="1">
        </div>
        
        <div class="form-row">
            <label for="school_full_name">School Full Name:</label>
            <input type="text" id="school_full_name" name="school_full_name" value="<?= htmlspecialchars($school_full_name) ?>" maxlength="150">
        </div>
        
        <div class="form-row">
            <label for="school_short_name">School Short Name:</label>
            <input type="text" id="school_short_name" name="school_short_name" value="<?= htmlspecialchars($school_short_name) ?>" maxlength="20">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-gray">Save New School Entry</button>
            <button type="reset" class="btn btn-outline">Reset Form</button>
            <a href="list.php" class="btn btn-red">Exit</a>
        </div>

    </form>
</div>

<?php include '../includes/footer.php'; ?>
