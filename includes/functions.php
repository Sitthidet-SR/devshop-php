<?php
// ไฟล์ฟังก์ชันทั่วไป (Helper Functions)

// ฟังก์ชันจัดรูปแบบราคา
function format_price($price)
{
    return number_format($price, 0, '.', ',');
}

// ฟังก์ชันสร้าง badge สำหรับคอร์ส
function get_course_badge($course)
{
    if ($course['bestseller']) {
        return '<span class="course-badge bestseller">ขายดี</span>';
    } elseif ($course['featured']) {
        return '<span class="course-badge">ยอดนิยม</span>';
    } elseif (strtotime($course['created_at']) > strtotime('-7 days')) {
        return '<span class="course-badge new">ใหม่</span>';
    }
    return '';
}

// ฟังก์ชันแสดงระดับความยาก
function get_level_text($level)
{
    $levels = [
        'beginner' => 'เริ่มต้น',
        'intermediate' => 'กลาง',
        'advanced' => 'ขั้นสูง'
    ];
    return $levels[$level] ?? 'ไม่ระบุ';
}
?>
