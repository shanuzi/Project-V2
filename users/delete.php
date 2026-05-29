<?php

require_once '../includes/auth.php';
require_login('../');
if (!can('Remover')) { header('Location: list.php'); exit; }
require_once '../config/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: list.php?msg=Invalid+user+ID.&type=error');
    exit;
}

if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === $id) {
    header('Location: list.php?msg=You+cannot+delete+your+own+account.&type=error');
    exit;
}

try {
    // Verify the user actually exists before attempting deletion
    $chk = $pdo->prepare("SELECT user_id, user_name FROM users WHERE user_id = ?");
    $chk->execute([$id]);
    $user = $chk->fetch();

    if (!$user) {
        header('Location: list.php?msg=User+not+found.&type=error');
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        header('Location: list.php?msg=User+deleted+successfully.&type=success');
    } else {
        header('Location: list.php?msg=User+could+not+be+deleted.&type=error');
    }

} catch (PDOException $e) {
    // Likely a foreign key constraint violation
    header('Location: list.php?msg=Delete+failed:+' . urlencode($e->getMessage()) . '&type=error');
}

exit;