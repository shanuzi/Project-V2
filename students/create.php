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

// Initialize data structure
$data = [
    'student_id'          => '',
    'student_first_name'  => '',
    'student_middle_name' => '',
    'student_last_name'   => '',
    'student_year'        => '',
    'prog_id'             => $_GET['prog_id'] ?? '' 
];

try {
    $programs = $pdo->query("
        SELECT p.prog_id, p.prog_short_name, p.prog_full_name 
        FROM programs p 
        ORDER BY p.prog_short_name ASC
    ")->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'Failed to load academic programs: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $data['student_id']          = trim($_POST['student_id'] ?? '');
    $data['student_first_name']  = trim($_POST['student_first_name'] ?? '');
    $data['student_middle_name'] = trim($_POST['student_middle_name'] ?? '');
    $data['student_last_name']   = trim($_POST['student_last_name'] ?? '');
    $data['student_year']        = trim($_POST['student_year'] ?? '');
    $data['prog_id']             = trim($_POST['prog_id'] ?? '');

    /* Validation */
    if ($data['student_id'] === '' || !ctype_digit($data['student_id'])) {
        $errors[] = 'Student ID must be a positive integer.';
    }
    if ($data['student_first_name'] === '') {
        $errors[] = 'Student First Name is required.';
    }
    if ($data['student_last_name'] === '') {
        $errors[] = 'Student Last Name is required.';
    }

    // constraint in years (BETWEEN 1 AND 6)
    if ($data['student_year'] === '' || !ctype_digit($data['student_year']) || $data['student_year'] < 1 || $data['student_year'] > 6) {
        $errors[] = 'Student Year must be an integer between 1 and 6.';
    }
    if ($data['prog_id'] === '' || !ctype_digit($data['prog_id'])) {
        $errors[] = 'Please select a valid Program.';
    }

    if (empty($errors)) {
        try {
            // Correct duplicate verification check against the students table
            $chk = $pdo->prepare("SELECT student_id FROM students WHERE student_id = ?");
            $chk->execute([$data['student_id']]);

            if ($chk->fetch()) {
                $errors[] = 'A student with that ID already exists.';
            } else {
                // Perform clear, explicit database entry insert operation
                $sql = "INSERT INTO students (student_id, student_first_name, student_middle_name, student_last_name, student_year, prog_id) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);

                // Set explicitly empty strings to NULL values for middle name if not provided
                $midName = $data['student_middle_name'] === '' ? null : $data['student_middle_name'];

                if ($stmt->execute([
                    $data['student_id'],
                    $data['student_first_name'],
                    $midName,
                    $data['student_last_name'],
                    $data['student_year'],
                    $data['prog_id']
                ])) {
                    // Redirect back to list view of the newly registered student's program
                    header('Location: list.php?prog_id=' . urlencode($data['prog_id']) . '&msg=Student+added+successfully.&type=success');
                    exit;
                }
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<p class="page-title">Student Create</p>

<?php if ($errors): ?>
    <div class="alert alert-error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="create.php<?= $data['prog_id'] !== '' ? '?prog_id=' . urlencode($data['prog_id']) : '' ?>">

        <div class="form-row">
            <label for="student_id">Student ID:</label>
            <input type="number" id="student_id" name="student_id"
                value="<?= htmlspecialchars($data['student_id']) ?>" min="1" required>
        </div>

        <div class="form-row">
            <label for="student_first_name">First Name:</label>
            <input type="text" id="student_first_name" name="student_first_name"
                value="<?= htmlspecialchars($data['student_first_name']) ?>" maxlength="80" required>
        </div>

        <div class="form-row">
            <label for="student_middle_name">Middle Name:</label>
            <input type="text" id="student_middle_name" name="student_middle_name"
                value="<?= htmlspecialchars($data['student_middle_name']) ?>" maxlength="80">
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
                <?php if (!empty($programs)): ?>
                    <?php foreach ($programs as $prog): ?>
                        <option value="<?= $prog['prog_id'] ?>" <?= $data['prog_id'] == $prog['prog_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($prog['prog_short_name'] . ' - ' . $prog['prog_full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-gray">Save New Student Entry</button>
            <button type="reset" class="btn btn-outline">Reset Form</button>
            <a href="list.php<?= $data['prog_id'] !== '' ? '?prog_id=' . urlencode($data['prog_id']) : '' ?>" class="btn btn-red">Exit</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>