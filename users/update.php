<?php
require_once '../includes/auth.php';
require_login('../');
if (!can('Updater')) { header('Location: list.php'); exit; }
require_once '../config/db.php';

$base_path = '../';
$errors = [];

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: list.php?msg=Invalid+user+ID.&type=error');
    exit;
}

// ---- Fetch existing user ----------------------------------
$chk = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$chk->execute([$id]);
$user = $chk->fetch();

if (!$user) {
    header('Location: list.php?msg=User+not+found.&type=error');
    exit;
}

// Pre-fill data from DB
$data = [
    'user_name' => $user['user_name'],
    'user_type' => $user['user_type'],
    'user_role' => $user['user_role'],
];

// ---- Handle POST -----------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['user_name'] = trim($_POST['user_name'] ?? '');
    $data['user_type'] = trim($_POST['user_type'] ?? 'User');
    $data['user_role'] = trim($_POST['user_role'] ?? 'Viewer');
    $new_password      = $_POST['user_password'] ?? ''; // blank = no change

    /* Validation */
    if ($data['user_name'] === '') {
        $errors[] = 'Username is required.';
    }

    if (empty($errors)) {
        try {
            // Duplicate username check — exclude the current user
            $dup = $pdo->prepare("SELECT user_id FROM users WHERE user_name = ? AND user_id != ?");
            $dup->execute([$data['user_name'], $id]);
            if ($dup->fetch()) {
                $errors[] = 'A user with that username already exists.';
            } else {
                if ($new_password !== '') {
                    // Update including new password
                    $stmt = $pdo->prepare("
                        UPDATE users
                        SET user_name = ?, user_password = SHA2(?, 256), user_type = ?, user_role = ?
                        WHERE user_id = ?
                    ");
                    $stmt->execute([$data['user_name'], $new_password, $data['user_type'], $data['user_role'], $id]);
                } else {
                    // Update without touching password
                    $stmt = $pdo->prepare("
                        UPDATE users
                        SET user_name = ?, user_type = ?, user_role = ?
                        WHERE user_id = ?
                    ");
                    $stmt->execute([$data['user_name'], $data['user_type'], $data['user_role'], $id]);
                }

                header('Location: list.php?msg=User+updated+successfully.&type=success');
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<p class="page-title">User Update</p>

<?php if ($errors): ?>
    <div class="alert alert-error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="update.php?id=<?= $id ?>">

        <div class="form-row">
            <label for="user_name">Username:</label>
            <input type="text" id="user_name" name="user_name"
                   value="<?= htmlspecialchars($data['user_name']) ?>" maxlength="50" required>
        </div>

        <div class="form-row">
            <label for="user_password">New Password:</label>
            <input type="password" id="user_password" name="user_password"
                   placeholder="Leave blank to keep current password">
        </div>

        <div class="form-row">
            <label for="user_type">User Type:</label>
            <select id="user_type" name="user_type">
                <option value="User"          <?= $data['user_type'] === 'User'          ? 'selected' : '' ?>>User</option>
                <option value="Administrator" <?= $data['user_type'] === 'Administrator' ? 'selected' : '' ?>>Administrator</option>
            </select>
        </div>

        <div class="form-row">
            <label for="user_role">User Role:</label>
            <select id="user_role" name="user_role">
                <option value="Viewer"        <?= $data['user_role'] === 'Viewer'        ? 'selected' : '' ?>>Viewer</option>
                <option value="Creator"       <?= $data['user_role'] === 'Creator'       ? 'selected' : '' ?>>Creator</option>
                <option value="Updater"       <?= $data['user_role'] === 'Updater'       ? 'selected' : '' ?>>Updater</option>
                <option value="Remover"       <?= $data['user_role'] === 'Remover'       ? 'selected' : '' ?>>Remover</option>
                <option value="Administrator" <?= $data['user_role'] === 'Administrator' ? 'selected' : '' ?>>Administrator</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-gray">Save Changes</button>
            <a href="list.php"    class="btn btn-red">Cancel</a>
        </div>

    </form>
</div>

<?php include '../includes/footer.php'; ?>