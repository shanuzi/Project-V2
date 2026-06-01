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

// Initialize data structure (no student_id — it is auto-generated)
$data = [
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


function generateNextStudentId(PDO $pdo): int {
    $year = date('Y'); // use current year dynamically
    $prefix = (int)($year . '0000');
    $ceiling = (int)($year . '9999');

    $stmt = $pdo->prepare("
        SELECT MAX(student_id) AS max_id
        FROM students
        WHERE student_id BETWEEN ? AND ?
    ");
    $stmt->execute([$prefix + 1, $ceiling]);
    $row = $stmt->fetch();

    if ($row && $row['max_id'] !== null) {
        return (int)$row['max_id'] + 1;
    }

    // No students for this year yet — start at YYYY0001
    return $prefix + 1;
}

$generated_id = null;
try {
    $generated_id = generateNextStudentId($pdo);
} catch (PDOException $e) {
    $errors[] = 'Failed to generate Student ID: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $data['student_first_name']  = trim($_POST['student_first_name'] ?? '');
    $data['student_middle_name'] = trim($_POST['student_middle_name'] ?? '');
    $data['student_last_name']   = trim($_POST['student_last_name'] ?? '');
    $data['student_year']        = trim($_POST['student_year'] ?? '');
    $data['prog_id']             = trim($_POST['prog_id'] ?? '');

    // Re-generate ID on POST in case of a concurrent insertion between page load and submit
    try {
        $generated_id = generateNextStudentId($pdo);
    } catch (PDOException $e) {
        $errors[] = 'Failed to generate Student ID: ' . $e->getMessage();
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
            // Duplicate check — guard against race condition
            $chk = $pdo->prepare("SELECT student_id FROM students WHERE student_id = ?");
            $chk->execute([$generated_id]);

            if ($chk->fetch()) {
                // Edge case: ID was taken between generation and insert; bump by 1
                $generated_id++;
            }

            $sql = "INSERT INTO students (student_id, student_first_name, student_middle_name, student_last_name, student_year, prog_id) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);

            $midName = $data['student_middle_name'] === '' ? null : $data['student_middle_name'];

            if ($stmt->execute([
                $generated_id,
                $data['student_first_name'],
                $midName,
                $data['student_last_name'],
                $data['student_year'],
                $data['prog_id']
            ])) {
                header('Location: list.php?prog_id=' . urlencode($data['prog_id']) . '&msg=Student+added+successfully.+ID:+' . $generated_id . '&type=success');
                exit;
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

        // student id auot gen
        <div class="form-row">
            <label for="student_id">Student ID:</label>
            <input type="text" id="student_id" name="student_id"
                value="<?= htmlspecialchars($generated_id ?? 'Generating…') ?>"
                readonly
                style="background:#f4f6f8; color:var(--muted); cursor:not-allowed; font-weight:700; letter-spacing:.04em;"
                title="Auto-generated — cannot be edited">
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