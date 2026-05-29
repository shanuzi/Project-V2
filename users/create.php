<?php

require_once '../includes/auth.php';
require_login('../');
if (!can('Creator')) {
    header('Location: list.php');
    exit;
}
require_once '../config/db.php'; // Provides the $pdo object

$base_path = '../';
$errors = [];
$success_count = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['user_csv']) && $_FILES['user_csv']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['user_csv']['tmp_name'];
        $file_name = $_FILES['user_csv']['name'];
        $ext       = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if ($ext !== 'csv') {
            $errors[] = 'Please upload a valid CSV file.';
        } else {
            if (($handle = fopen($file_tmp, 'r')) !== FALSE) {
               
                $headers = fgetcsv($handle, 1000, ',');

                try {
                  
                    $pdo->beginTransaction();

                  
                    $chk  = $pdo->prepare("SELECT user_id FROM users WHERE user_name = ?");
                    $ins  = $pdo->prepare("INSERT INTO users (user_name, user_password, user_type, user_role) VALUES (?, SHA2(?, 256), ?, ?)");

                    $row_num = 1; // Keeping track for helpful error messaging
                    while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
                        $row_num++;
                        //validation array
                        $allowed_types = ['User', 'Administrator'];
                        $allowed_roles = ['Viewer', 'Creator', 'Updater', 'Remover', 'Administrator'];
                        //extract
                        $u_name = trim($row[0] ?? '');
                        $u_pass = $row[1] ?? '';
                        $u_type = ucwords(strtolower(trim($row[2] ?? 'User')));
                        $u_role = ucwords(strtolower(trim($row[3] ?? 'Viewer')));
                        //validate input
                        if (empty($u_name) || empty($u_pass)) {
                            throw new Exception("Row {$row_num}: Username and Password cannot be blank.");
                        }

                        if (!in_array($u_type, $allowed_types)) {
                            throw new Exception("Row {$row_num}: Invalid user type '{$u_type}'. Allowed: " . implode(', ', $allowed_types));
                        }

                        if (!in_array($u_role, $allowed_roles)) {
                            throw new Exception("Row {$row_num}: Invalid user role '{$u_role}'. Allowed: " . implode(', ', $allowed_roles));
                        }

                        $chk->execute([$u_name]);
                        if ($chk->fetch()) {
                            throw new Exception("Row {$row_num}: A user with the username '{$u_name}' already exists.");
                        }

                        $ins->execute([$u_name, $u_pass, $u_type, $u_role]);
                        $success_count++;
                    }

                    $pdo->commit();
                    fclose($handle);

                    header('Location: list.php?msg=' . urlencode("Successfully imported {$success_count} users.") . '&type=success');
                    exit;
                } catch (Exception $e) {
                    $pdo->rollBack();
                    fclose($handle);
                    $errors[] = $e->getMessage();
                }
            } else {
                $errors[] = 'Failed to open the uploaded file.';
            }
        }
    } else {
        $errors[] = 'Please select a CSV file to upload.';
    }
}

include '../includes/header.php';
?>

<p class="page-title">Bulk User Import (CSV)</p>

<?php if ($errors): ?>
    <div class="alert alert-error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="create.php" enctype="multipart/form-data">

        <div style="margin-bottom: 20px; font-size: 0.9em; color: #555;">
            <p><strong>CSV File Template Requirements:</strong></p>
            <table style="width:100%; border-collapse: collapse; text-align: left; margin-top: 10px;">
                <thead>
                    <tr style="border-bottom: 2px solid #ccc;">
                        <th>Column 1</th>
                        <th>Column 2</th>
                        <th>Column 3</th>
                        <th>Column 4</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>username</td>
                        <td>password</td>
                        <td>user_type <br><small>(User / Administrator)</small></td>
                        <td>user_role <br><small>(Viewer / Creator / Updater / Remover / Administrator)</small></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="form-row">
            <label for="user_csv">Select CSV File:</label>
            <input type="file" id="user_csv" name="user_csv" accept=".csv" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-gray">Upload and Import Users</button>
            <a href="list.php" class="btn btn-red">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>