<?php
// ฟังก์ชันสำหรับอัพโหลดไฟล์

function upload_image($file, $folder = 'courses', $max_size = 104857600)
{
    // ตรวจสอบว่ามีไฟล์หรือไม่
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => false, 'message' => 'ไม่มีไฟล์ที่อัพโหลด'];
    }

    // ตรวจสอบ error แบบละเอียด
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'ไฟล์มีขนาดใหญ่เกินที่กำหนดในเซิร์ฟเวอร์ (สูงสุด ' . ini_get('upload_max_filesize') . ')',
            UPLOAD_ERR_FORM_SIZE => 'ไฟล์มีขนาดใหญ่เกินที่กำหนด',
            UPLOAD_ERR_PARTIAL => 'อัพโหลดไฟล์ไม่สมบูรณ์',
            UPLOAD_ERR_NO_FILE => 'ไม่มีไฟล์ที่อัพโหลด',
            UPLOAD_ERR_NO_TMP_DIR => 'ไม่พบโฟลเดอร์ชั่วคราว',
            UPLOAD_ERR_CANT_WRITE => 'ไม่สามารถเขียนไฟล์ลงดิสก์',
            UPLOAD_ERR_EXTENSION => 'การอัพโหลดถูกหยุดโดย extension'
        ];
        $message = isset($error_messages[$file['error']]) ? $error_messages[$file['error']] : 'เกิดข้อผิดพลาดในการอัพโหลด';
        return ['success' => false, 'message' => $message];
    }

    // ตรวจสอบขนาดไฟล์ (default 100MB)
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'ไฟล์มีขนาด ' . round($file['size'] / 1048576, 2) . ' MB ใหญ่เกินไป (สูงสุด ' . round($max_size / 1048576) . ' MB)'];
    }

    // ตรวจสอบประเภทไฟล์
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        return ['success' => false, 'message' => 'ประเภทไฟล์ไม่ถูกต้อง (รองรับเฉพาะ JPG, PNG, GIF, WebP)'];
    }

    // สร้างชื่อไฟล์ใหม่
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid() . '_' . time() . '.' . $extension;

    // กำหนด path
    $upload_dir = __DIR__ . '/../uploads/' . $folder . '/';
    $upload_path = $upload_dir . $new_filename;

    // สร้างโฟลเดอร์ถ้ายังไม่มี
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            return ['success' => false, 'message' => 'ไม่สามารถสร้างโฟลเดอร์ได้: ' . $upload_dir];
        }
        @chmod($upload_dir, 0777);
    }
    
    // ตรวจสอบว่าโฟลเดอร์เขียนได้หรือไม่
    if (!is_writable($upload_dir)) {
        return ['success' => false, 'message' => 'โฟลเดอร์ไม่สามารถเขียนได้: ' . $upload_dir];
    }

    // ตรวจสอบว่าไฟล์ temp มีอยู่จริง
    if (!file_exists($file['tmp_name'])) {
        return ['success' => false, 'message' => 'ไม่พบไฟล์ชั่วคราว: ' . $file['tmp_name']];
    }
    
    // ย้ายไฟล์
    $move_success = false;
    $error_detail = '';
    
    // ลองใช้ move_uploaded_file ก่อน (สำหรับไฟล์ที่อัพโหลดจริง)
    if (is_uploaded_file($file['tmp_name'])) {
        $move_success = @move_uploaded_file($file['tmp_name'], $upload_path);
        if (!$move_success) {
            $error_detail = 'move_uploaded_file failed';
        }
    } else {
        // ถ้าไม่ใช่ uploaded file (เช่น ทดสอบ CLI) ใช้ copy แทน
        $move_success = @copy($file['tmp_name'], $upload_path);
        if (!$move_success) {
            $error_detail = 'copy failed';
        } else if (file_exists($file['tmp_name'])) {
            @unlink($file['tmp_name']);
        }
    }
    
    if ($move_success) {
        // ตั้งค่า permission
        @chmod($upload_path, 0644);
        
        return [
            'success' => true,
            'filename' => $new_filename,
            'path' => 'uploads/' . $folder . '/' . $new_filename,
            'url' => 'uploads/' . $folder . '/' . $new_filename
        ];
    } else {
        $debug_info = [
            'upload_dir' => $upload_dir,
            'upload_path' => $upload_path,
            'dir_exists' => file_exists($upload_dir) ? 'yes' : 'no',
            'dir_writable' => is_writable($upload_dir) ? 'yes' : 'no',
            'tmp_file_exists' => file_exists($file['tmp_name']) ? 'yes' : 'no',
            'is_uploaded' => is_uploaded_file($file['tmp_name']) ? 'yes' : 'no'
        ];
        
        $error_msg = 'ไม่สามารถบันทึกไฟล์ได้ ';
        $error_msg .= 'โฟลเดอร์: ' . ($debug_info['dir_writable'] === 'yes' ? 'เขียนได้' : 'เขียนไม่ได้');
        if ($error_detail) {
            $error_msg .= ' (' . $error_detail . ')';
        }
        
        return ['success' => false, 'message' => $error_msg, 'debug' => $debug_info];
    }
}

function delete_image($path)
{
    $full_path = __DIR__ . '/../' . $path;
    if (file_exists($full_path)) {
        return unlink($full_path);
    }
    return false;
}

function get_image_url($path)
{
    if (empty($path)) {
        return 'https://via.placeholder.com/400x250/667eea/ffffff?text=No+Image';
    }

    // ถ้าเป็น URL เต็มแล้ว
    if (strpos($path, 'http') === 0) {
        return $path;
    }

    // ถ้าเป็น path ในเซิร์ฟเวอร์
    return $path;
}
