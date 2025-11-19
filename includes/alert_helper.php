<?php
/**
 * Alert Helper Functions
 * ฟังก์ชันช่วยเหลือสำหรับแสดง SweetAlert2 จาก PHP
 */

// ฟังก์ชันสร้าง alert data สำหรับ JavaScript
function set_alert($type, $message, $title = null) {
    $_SESSION['alert'] = [
        'type' => $type,
        'message' => $message,
        'title' => $title
    ];
}

// ฟังก์ชันแสดง alert และลบออกจาก session
function show_alert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        
        echo '<div id="php-alert-data" 
                   data-type="' . htmlspecialchars($alert['type']) . '" 
                   data-message="' . htmlspecialchars($alert['message']) . '" 
                   data-title="' . htmlspecialchars($alert['title'] ?? '') . '" 
                   style="display:none;"></div>';
    }
}

// ฟังก์ชันแสดง alert แบบ inline (ไม่ใช้ session)
function show_alert_inline($type, $message, $title = null) {
    echo '<div id="php-alert-data" 
               data-type="' . htmlspecialchars($type) . '" 
               data-message="' . htmlspecialchars($message) . '" 
               data-title="' . htmlspecialchars($title ?? '') . '" 
               style="display:none;"></div>';
}

// ฟังก์ชันสำหรับ success alert
function alert_success($message, $title = 'สำเร็จ!') {
    set_alert('success', $message, $title);
}

// ฟังก์ชันสำหรับ error alert
function alert_error($message, $title = 'เกิดข้อผิดพลาด!') {
    set_alert('error', $message, $title);
}

// ฟังก์ชันสำหรับ warning alert
function alert_warning($message, $title = 'คำเตือน!') {
    set_alert('warning', $message, $title);
}

// ฟังก์ชันสำหรับ info alert
function alert_info($message, $title = 'ข้อมูล') {
    set_alert('info', $message, $title);
}
