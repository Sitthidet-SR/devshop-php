<?php
session_start();

// ล้าง output buffer ก่อน
ob_clean();

header('Content-Type: application/json');

require_once 'includes/config.php';
require_once 'includes/auth_functions.php';

// ตรวจสอบว่า login หรือไม่
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit;
}

$user_id = $_SESSION['user_id'];
$course_id = intval($_POST['course_id'] ?? 0);
$lecture_id = intval($_POST['lecture_id'] ?? 0);

if (!$course_id || !$lecture_id) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

// ตรวจสอบว่าได้ลงทะเบียนคอร์สนี้หรือไม่
$check_sql = "SELECT enrollment_id FROM enrollments 
              WHERE user_id = $user_id AND course_id = $course_id";
$check_result = $conn->query($check_sql);

if ($check_result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'คุณยังไม่ได้ลงทะเบียนคอร์สนี้']);
    exit;
}

$enrollment = $check_result->fetch_assoc();
$enrollment_id = $enrollment['enrollment_id'];

// บันทึกว่าเรียนบทเรียนนี้แล้ว
$insert_sql = "INSERT INTO lecture_progress (enrollment_id, lecture_id, completed) 
               VALUES ($enrollment_id, $lecture_id, 1)
               ON DUPLICATE KEY UPDATE completed = 1, completed_at = CURRENT_TIMESTAMP";

if ($conn->query($insert_sql)) {
    // คำนวณ progress ใหม่
    $progress_sql = "SELECT 
                        (SELECT COUNT(*) FROM lecture_progress 
                         WHERE enrollment_id = $enrollment_id AND completed = 1) as completed_lectures,
                        (SELECT COUNT(*) FROM lectures l
                         JOIN sections s ON l.section_id = s.section_id
                         WHERE s.course_id = $course_id) as total_lectures";
    
    $progress_result = $conn->query($progress_sql);
    $progress_data = $progress_result->fetch_assoc();
    
    $completed = $progress_data['completed_lectures'];
    $total = $progress_data['total_lectures'];
    $progress = $total > 0 ? round(($completed / $total) * 100) : 0;
    
    // อัพเดท progress ใน enrollments
    $update_sql = "UPDATE enrollments 
                   SET progress = $progress
                   WHERE enrollment_id = $enrollment_id";
    
    $conn->query($update_sql);
    
    echo json_encode([
        'success' => true, 
        'progress' => $progress,
        'completed' => $completed,
        'total' => $total,
        'debug' => [
            'enrollment_id' => $enrollment_id,
            'course_id' => $course_id,
            'lecture_id' => $lecture_id
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $conn->error]);
}

// ปิดการเชื่อมต่อ
$conn->close();
