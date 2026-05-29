<?php
require_once '../includes/auth.php';
require_login('../');
if (!can('Remover')) { header('Location: list.php'); exit; }
require_once '../config/db.php';

$id = $_GET['id'] ?? '';

if ($id === '') {
    header('Location: list.php?msg=Invalid+request.&type=error');
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM schools WHERE school_id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        header('Location: list.php?msg=School+deleted+successfully.&type=success');
    } else {
        header('Location: list.php?msg=School+not+found.&type=error');
    }
} catch (PDOException $e) {
    // Foreign key constraint likely (departments exist under this school)
    header('Location: list.php?msg=Cannot+delete:+school+has+linked+departments.&type=error');
}
exit;
?>