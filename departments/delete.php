<?php

require_once '../includes/auth.php';
require_login('../');
if (!can('Remover')) { header('Location: list.php'); exit; }
require_once '../config/db.php';

$id = $_GET['id'] ?? '';

if ($id === '') { header('Location: list.php?msg=Invalid+request.&type=error'); exit; }

try {
    $stmt = $pdo->prepare("DELETE FROM departments WHERE dept_id = ?");
    $stmt->execute([$id]);
    if ($stmt->rowCount() > 0) {
        header('Location: list.php?msg=Department+deleted+successfully.&type=success');
    } else {
        header('Location: list.php?msg=Department+not+found.&type=error');
    }
} catch (PDOException $e) {
    header('Location: list.php?msg=Cannot+delete:+department+has+linked+programs.&type=error');
}
exit;
?>