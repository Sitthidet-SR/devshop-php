<?php

function get_course_reviews($course_id, $limit = null) {
    global $conn;
    
    $sql = "SELECT r.*, u.first_name, u.last_name, u.profile_image 
            FROM reviews r 
            JOIN users u ON r.user_id = u.user_id 
            WHERE r.course_id = $course_id AND r.status = 'approved' 
            ORDER BY r.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT $limit";
    }
    
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function get_course_rating_summary($course_id) {
    global $conn;
    
    $sql = "SELECT 
            COUNT(*) as total_reviews,
            AVG(rating) as avg_rating,
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
            FROM reviews 
            WHERE course_id = $course_id AND status = 'approved'";
    
    $result = $conn->query($sql);
    return $result ? $result->fetch_assoc() : null;
}

function render_stars($rating) {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $html .= '<i class="fas fa-star" style="color: #ffc107;"></i>';
        } else {
            $html .= '<i class="far fa-star" style="color: #ddd;"></i>';
        }
    }
    return $html;
}

function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'เมื่อสักครู่';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' นาทีที่แล้ว';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' ชั่วโมงที่แล้ว';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' วันที่แล้ว';
    } else {
        return date('d/m/Y', $timestamp);
    }
}
?>
