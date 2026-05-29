<?php

require_once '../includes/auth.php';
require_login('../');
if (!can('Remover')) { header('Location: list.php'); exit; }
require_once '../config/db.php';

$id = $_GET['student_id'] ?? '';
$prog_id = $_GET['prog_id'];
if ($id === '') { header('Location: list.php?msg=Invalid+request.&type=error'); exit; }

try {

    $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() > 0) {
        header("Location: list.php?prog_id=" . urlencode($prog_id) . "&msg=Student+deleted+successfully.&type=success");
    } else {
        header("Location: list.php?prog_id=" . urlencode($prog_id) . "&msg=Student+not+found.&type=error");
    }
} catch (PDOException $e) {
    header('Location: list.php?msg=Cannot+delete:+students+are+enrolled+in+this+program.&type=error');
}
exit;
?>