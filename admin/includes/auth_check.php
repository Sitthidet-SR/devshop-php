<?php
// ตรวจสอบว่า login และเป็น admin หรือไม่
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin/index.php');
    exit;
}

if ($_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}
?>
