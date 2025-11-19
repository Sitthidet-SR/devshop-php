<?php
session_start();

// เพิ่มขนาดไฟล์ที่อัพโหลดได้
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
ini_set('max_execution_time', '300');
ini_set('max_input_time', '300');

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: courses.php');
    exit;
}

$course_id = intval($_POST['course_id']);
$section_id = intval($_POST['section_id']);
$lecture_title = clean_input($_POST['lecture_title']);
$lecture_type = clean_input($_POST['lecture_type']);
$content_text = clean_input($_POST['content_text']);
$duration_minutes = intval($_POST['duration_minutes']);

$content_url = null;

// อัพโหลดวิดีโอ
if ($lecture_type == 'video' && isset($_FILES['video_file']) && $_FILES['video_file']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['video_file'];
    
    // ตรวจสอบ error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        header("Location: course_content.php?id=$course_id&error=upload_failed");
        exit;
    }
    
    // ตรวจสอบประเภทไฟล์
    $allowed_types = ['video/mp4', 'video/webm', 'video/ogg'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        header("Location: course_content.php?id=$course_id&error=invalid_type");
        exit;
    }
    
    // ตรวจสอบขนาดไฟล์ (100MB)
    $max_size = 100 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        header("Location: course_content.php?id=$course_id&error=file_too_large");
        exit;
    }
    
    // สร้างชื่อไฟล์ใหม่
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'video_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
    $upload_path = '../uploads/videos/' . $new_filename;
    
    // อัพโหลดไฟล์
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        $content_url = 'uploads/videos/' . $new_filename;
    } else {
        header("Location: course_content.php?id=$course_id&error=upload_failed");
        exit;
    }
}

// หาลำดับบทเรียนล่าสุด
$order_result = $conn->query("SELECT MAX(lecture_order) as max_order FROM lectures WHERE section_id = $section_id");
$max_order = $order_result->fetch_assoc()['max_order'] ?? 0;
$lecture_order = $max_order + 1;

// เพิ่มบทเรียน
$sql = "INSERT INTO lectures (section_id, lecture_title, lecture_type, content_url, content_text, duration_minutes, lecture_order) 
        VALUES ($section_id, '$lecture_title', '$lecture_type', " . 
        ($content_url ? "'$content_url'" : "NULL") . ", '$content_text', $duration_minutes, $lecture_order)";

if ($conn->query($sql)) {
    header("Location: course_content.php?id=$course_id&msg=lecture_added");
} else {
    header("Location: course_content.php?id=$course_id&error=db_error");
}
exit;
?>
