<?php

require_once '../includes/auth.php';
require_login('../');
if (!can('Remover')) { header('Location: list.php'); exit; }
require_once '../config/db.php';

$id = $_GET['id'] ?? '';

if ($id === '') { header('Location: list.php?msg=Invalid+request.&type=error'); exit; }

try {
    $stmt = $pdo->prepare("DELETE FROM programs WHERE prog_id = ?");
    $stmt->execute([$id]);
    if ($stmt->rowCount() > 0) {
        header('Location: list.php?msg=Program+deleted+successfully.&type=success');
    } else {
        header('Location: list.php?msg=Program+not+found.&type=error');
    }
} catch (PDOException $e) {
    header('Location: list.php?msg=Cannot+delete:+students+are+enrolled+in+this+program.&type=error');
}
exit;
?>