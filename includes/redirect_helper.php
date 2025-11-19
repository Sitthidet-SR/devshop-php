<?php

/**
 * Redirect Helper Functions
 * ฟังก์ชันช่วยเหลือสำหรับ redirect ที่ปลอดภัยและไม่มี query string ซ้ำซ้อน
 */

/**
 * Redirect พร้อม message parameter
 * @param string $url - URL ที่ต้องการ redirect
 * @param string $msg_code - Message code
 * @param array $additional_params - Parameters เพิ่มเติม (optional)
 */
function redirect_with_message($url, $msg_code, $additional_params = [])
{
    // แยก URL และ query string
    $url_parts = parse_url($url);

    // สร้าง base URL
    if (isset($url_parts['scheme']) && isset($url_parts['host'])) {
        // URL เต็ม (มี http:// หรือ https://)
        $base_url = $url_parts['scheme'] . '://' . $url_parts['host'];
        if (isset($url_parts['port'])) {
            $base_url .= ':' . $url_parts['port'];
        }
        $base_url .= $url_parts['path'] ?? '/';
    } else {
        // URL แบบ relative
        $base_url = $url_parts['path'] ?? $url;
    }

    // รวม query parameters
    $params = ['msg' => $msg_code];

    // เพิ่ม parameters เดิมถ้ามี (ยกเว้น msg)
    if (isset($url_parts['query'])) {
        parse_str($url_parts['query'], $existing_params);
        unset($existing_params['msg']); // ลบ msg เดิมออก
        $params = array_merge($existing_params, $params);
    }

    // เพิ่ม parameters เพิ่มเติม
    if (!empty($additional_params)) {
        $params = array_merge($params, $additional_params);
    }

    // สร้าง URL ใหม่
    $query_string = http_build_query($params);
    $final_url = $base_url . ($query_string ? '?' . $query_string : '');

    header('Location: ' . $final_url);
    exit;
}

/**
 * Redirect กลับไปหน้าเดิมพร้อม message
 * @param string $msg_code - Message code
 * @param string $default_url - URL เริ่มต้นถ้าไม่มี referer
 */
function redirect_back_with_message($msg_code, $default_url = 'index.php')
{
    $referer = $_SERVER['HTTP_REFERER'] ?? null;

    if ($referer) {
        // ถ้า referer เป็น URL เต็ม ให้ใช้ทั้งหมด
        redirect_with_message($referer, $msg_code);
    } else {
        // ถ้าไม่มี referer ใช้ default URL
        redirect_with_message($default_url, $msg_code);
    }
}

/**
 * Redirect ไปหน้าเดียวกันพร้อม message (สำหรับ self-redirect)
 * @param string $msg_code - Message code
 */
function redirect_self_with_message($msg_code)
{
    $current_url = $_SERVER['PHP_SELF'];
    redirect_with_message($current_url, $msg_code);
}

/**
 * สร้าง URL พร้อม message parameter
 * @param string $url - URL ที่ต้องการ
 * @param string $msg_code - Message code
 * @return string - URL ที่สมบูรณ์
 */
function url_with_message($url, $msg_code)
{
    $separator = strpos($url, '?') !== false ? '&' : '?';
    return $url . $separator . 'msg=' . urlencode($msg_code);
}
