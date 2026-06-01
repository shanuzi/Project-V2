<?php
require_once '../includes/auth.php';
require_login('../');
if (!can('Creator')) { header('Location: list.php'); exit; }
require_once '../config/db.php';

$base_path = '../';
$errors = [];

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

// --- Generate next student_id ---
// Format: {year}-{4-digit sequence}
// Uses MAX of the numeric part to stay correct after deletions.
// e.g. MAX is 2026-0003 → extract 0003 → next is 0004 → 2026-0004
$year = date('Y');
$maxStmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(student_id, 6) AS UNSIGNED)) FROM students");
$maxSeq  = (int) $maxStmt->fetchColumn(); // returns 0 if table is empty
$student_id_preview = $year . '-' . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['student_first_name']  = trim($_POST['student_first_name']  ?? '');
    $data['student_middle_name'] = trim($_POST['student_middle_name'] ?? '');
    $data['student_last_name']   = trim($_POST['student_last_name']   ?? '');
    $data['student_year']        = trim($_POST['student_year']        ?? '');
    $data['prog_id']             = trim($_POST['prog_id']             ?? '');

    if ($data['student_first_name'] === '') $errors[] = 'Student First Name is required.';
    if ($data['student_last_name']  === '') $errors[] = 'Student Last Name is required.';
    if ($data['student_year'] === '' || !ctype_digit($data['student_year'])
        || $data['student_year'] < 1 || $data['student_year'] > 6)
        $errors[] = 'Student Year must be between 1 and 6.';
    if ($data['prog_id'] === '' || !ctype_digit($data['prog_id']))
        $errors[] = 'Please select a valid Program.';

    if (empty($errors)) {
        // Re-compute at save time using MAX to avoid race conditions and deletion gaps
        $maxStmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(student_id, 6) AS UNSIGNED)) FROM students");
        $maxSeq  = (int) $maxStmt->fetchColumn();
        $student_id = date('Y') . '-' . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);

        try {
            $chk = $pdo->prepare("SELECT student_id FROM students WHERE student_id = ?");
            $chk->execute([$student_id]);
            if ($chk->fetch()) {
                $errors[] = 'Generated Student ID already exists. Please try again.';
            } else {
                $midName = $data['student_middle_name'] === '' ? null : $data['student_middle_name'];
                $stmt = $pdo->prepare("
                    INSERT INTO students
                        (student_id, student_first_name, student_middle_name, student_last_name, student_year, prog_id)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                if ($stmt->execute([
                    $student_id,
                    $data['student_first_name'],
                    $midName,
                    $data['student_last_name'],
                    $data['student_year'],
                    $data['prog_id']
                ])) {
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

        <!-- Auto-generated student ID — read-only -->
        <div class="form-row">
            <label>Student ID:</label>
            <input type="text" value="<?= htmlspecialchars($student_id_preview) ?>" disabled
                   style="background:#f4f6f8;color:var(--muted);cursor:not-allowed;font-weight:700;">
            <span class="field-error" style="color:var(--muted);font-size:11px;">Auto-generated</span>
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
                <?php foreach ($programs as $prog): ?>
                    <option value="<?= $prog['prog_id'] ?>"
                        <?= $data['prog_id'] == $prog['prog_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($prog['prog_short_name'] . ' - ' . $prog['prog_full_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-gray">Save New Student Entry</button>
            <button type="reset"  class="btn btn-outline">Reset Form</button>
            <a href="list.php<?= $data['prog_id'] !== '' ? '?prog_id=' . urlencode($data['prog_id']) : '' ?>"
               class="btn btn-red">Exit</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>