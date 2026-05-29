<?php
// ============================================================
// students/update.php  –  Edit an existing student
// ============================================================

require_once '../includes/auth.php';
require_login('../');
if (!can('Updater')) { header('Location: list.php'); exit; }
require_once '../config/db.php';

$base_path = '../';
$errors = [];
$id = $_GET['student_id'] ?? $_POST['student_id'] ?? '';

// Fetch academic programs to populate the dropdown selection menu
try {
    $programs = $pdo->query("
        SELECT p.prog_id, p.prog_short_name, p.prog_full_name 
        FROM programs p 
        ORDER BY p.prog_short_name ASC
    ")->fetchAll();
} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}

// Locate the existing student target entry record
try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    if (!$data) { header('Location: list.php?msg=Student+not+found.&type=error'); exit; }
} catch (PDOException $e) { die('Database error: ' . $e->getMessage()); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['student_first_name']  = trim($_POST['student_first_name']  ?? '');
    $data['student_middle_name'] = trim($_POST['student_middle_name'] ?? '');
    $data['student_last_name']   = trim($_POST['student_last_name']   ?? '');
    $data['student_year']        = trim($_POST['student_year']        ?? '');
    $data['prog_id']             = trim($_POST['prog_id']             ?? '');

    /* Validation */
    if ($data['student_first_name'] === '') { $errors[] = 'Student First Name is required.'; }
    if ($data['student_last_name'] === '')  { $errors[] = 'Student Last Name is required.'; }
    
    // Check against standard CHECK constraint in DB (BETWEEN 1 AND 6)
    if ($data['student_year'] === '' || !ctype_digit($data['student_year']) || $data['student_year'] < 1 || $data['student_year'] > 6) {
        $errors[] = 'Student Year must be an integer between 1 and 6.';
    }
    if ($data['prog_id'] === '') { $errors[] = 'Please select an Academic Program.'; }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE students SET student_first_name=?, student_middle_name=?, student_last_name=?, student_year=?, prog_id=? WHERE student_id=?");
            
            // Convert empty middle name inputs into explicit SQL NULL values
            $midName = $data['student_middle_name'] === '' ? null : $data['student_middle_name'];
            
            $stmt->execute([
                $data['student_first_name'], 
                $midName, 
                $data['student_last_name'], 
                $data['student_year'], 
                $data['prog_id'], 
                $id
            ]);
            
            header('Location: list.php?msg=Student+updated+successfully.&type=success');
            exit;
        } catch (PDOException $e) { $errors[] = 'Database error: ' . $e->getMessage(); }
    }
}

include '../includes/header.php';
?>

<p class="page-title">Student Update</p>

<?php if ($errors): ?>
    <div class="alert alert-error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="update.php">
        <input type="hidden" name="student_id" value="<?= htmlspecialchars($data['student_id']) ?>">

        <div class="form-row">
            <label>Student ID:</label>
            <input type="text" value="<?= htmlspecialchars($data['student_id']) ?>" disabled>
        </div>

        <div class="form-row">
            <label for="student_first_name">First Name:</label>
            <input type="text" id="student_first_name" name="student_first_name"
                   value="<?= htmlspecialchars($data['student_first_name']) ?>" maxlength="80" required>
        </div>

        <div class="form-row">
            <label for="student_middle_name">Middle Name:</label>
            <input type="text" id="student_middle_name" name="student_middle_name"
                   value="<?= htmlspecialchars($data['student_middle_name'] ?? '') ?>" maxlength="80">
        </div>

        <div class="form-row">
            <label for="student_last_name">Last Name:</label>
            <input type="text" id="student_last_name" name="student_last_name"
                   value="<?= htmlspecialchars($data['student_last_name']) ?>" maxlength="80" required>
        </div>

        <div class="form-row">
            <label for="student_year">Year Level:</label>
            <select id="student_year" name="student_year" required>
                <option value="">-- Select Year Level --</option>
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <option value="<?= $i ?>" <?= (int)$data['student_year'] === $i ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="form-row">
            <label for="prog_id">Academic Program:</label>
            <select id="prog_id" name="prog_id" required>
                <option value="">-- Select Program --</option>
                <?php foreach ($programs as $p): ?>
                    <option value="<?= $p['prog_id'] ?>"
                        <?= $data['prog_id'] == $p['prog_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['prog_short_name'] . ' – ' . $p['prog_full_name']) ?>
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