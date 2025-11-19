<?php
/**
 * SweetAlert Display Helper
 * ฟังก์ชันสำหรับแสดง SweetAlert2 จาก PHP variables
 */

// แสดง alert สำหรับ $message และ $error
function display_alerts($message = '', $error = '') {
    if (!empty($message)) {
        echo '<div id="php-alert-data" 
                   data-type="success" 
                   data-message="' . htmlspecialchars($message) . '" 
                   style="display:none;"></div>';
    }
    
    if (!empty($error)) {
        echo '<div id="php-alert-data" 
                   data-type="error" 
                   data-message="' . htmlspecialchars($error) . '" 
                   style="display:none;"></div>';
    }
}

// แสดง alert จาก session
function display_session_alerts() {
    if (isset($_SESSION['success_message'])) {
        echo '<div id="php-alert-data" 
                   data-type="success" 
                   data-message="' . htmlspecialchars($_SESSION['success_message']) . '" 
                   style="display:none;"></div>';
        unset($_SESSION['success_message']);
    }
    
    if (isset($_SESSION['error_message'])) {
        echo '<div id="php-alert-data" 
                   data-type="error" 
                   data-message="' . htmlspecialchars($_SESSION['error_message']) . '" 
                   style="display:none;"></div>';
        unset($_SESSION['error_message']);
    }
    
    if (isset($_SESSION['warning_message'])) {
        echo '<div id="php-alert-data" 
                   data-type="warning" 
                   data-message="' . htmlspecialchars($_SESSION['warning_message']) . '" 
                   style="display:none;"></div>';
        unset($_SESSION['warning_message']);
    }
    
    if (isset($_SESSION['info_message'])) {
        echo '<div id="php-alert-data" 
                   data-type="info" 
                   data-message="' . htmlspecialchars($_SESSION['info_message']) . '" 
                   style="display:none;"></div>';
        unset($_SESSION['info_message']);
    }
}

// ฟังก์ชันสำหรับแสดง inline alert (ไม่ใช้ session)
function show_inline_alert($type, $message, $title = null) {
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                icon: "' . $type . '",
                title: "' . ($title ? addslashes($title) : ($type === 'success' ? 'สำเร็จ!' : ($type === 'error' ? 'เกิดข้อผิดพลาด!' : 'แจ้งเตือน'))) . '",
                text: "' . addslashes($message) . '",
                confirmButtonColor: "#667eea"' .
                ($type === 'success' ? ',
                timer: 2000,
                timerProgressBar: true' : '') . '
            });
        });
    </script>';
}
?>
