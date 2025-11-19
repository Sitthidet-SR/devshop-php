<?php
session_start();

require_once 'includes/config.php';
require_once 'includes/auth_functions.php';

// ตรวจสอบว่า login หรือไม่
if (!is_logged_in()) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$user_id = $_SESSION['user_id'];
$course_id = intval($_GET['course_id'] ?? 0);

if ($course_id > 0) {
    // ตรวจสอบว่ามีในตะกร้าแล้วหรือไม่
    $check = $conn->query("SELECT * FROM cart WHERE user_id = $user_id AND course_id = $course_id");

    if ($check->num_rows == 0) {
        // เพิ่มลงตะกร้า
        $conn->query("INSERT INTO cart (user_id, course_id) VALUES ($user_id, $course_id)");
        $msg_code = 'added_to_cart';
    } else {
        $msg_code = 'already_in_cart';
    }
} else {
    $msg_code = 'error';
}

require_once 'includes/redirect_helper.php';
redirect_back_with_message($msg_code, 'index.php');
