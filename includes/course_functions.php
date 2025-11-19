<?php
// ฟังก์ชันเกี่ยวกับคอร์ส

// ฟังก์ชันดึงคอร์สทั้งหมด (สำหรับหน้าบ้าน - เฉพาะ published)
function get_all_courses($limit = null)
{
    global $conn;

    $sql = "SELECT c.*, cat.category_name, cat.icon,
            CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
            COALESCE(AVG(r.rating), 0) as avg_rating,
            COUNT(DISTINCT e.enrollment_id) as total_students
            FROM courses c
            LEFT JOIN categories cat ON c.category_id = cat.category_id
            LEFT JOIN users u ON c.instructor_id = u.user_id
            LEFT JOIN reviews r ON c.course_id = r.course_id AND r.status = 'approved'
            LEFT JOIN enrollments e ON c.course_id = e.course_id
            WHERE c.status = 'published'
            GROUP BY c.course_id
            ORDER BY c.created_at DESC";

    if ($limit) {
        $sql .= " LIMIT " . intval($limit);
    }

    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// ฟังก์ชันดึงคอร์สทั้งหมด (สำหรับหลังบ้าน - ทุก status)
function get_all_courses_admin($limit = null)
{
    global $conn;

    $sql = "SELECT c.*, cat.category_name, cat.icon,
            CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
            COALESCE(AVG(r.rating), 0) as avg_rating,
            COUNT(DISTINCT e.enrollment_id) as total_students
            FROM courses c
            LEFT JOIN categories cat ON c.category_id = cat.category_id
            LEFT JOIN users u ON c.instructor_id = u.user_id
            LEFT JOIN reviews r ON c.course_id = r.course_id AND r.status = 'approved'
            LEFT JOIN enrollments e ON c.course_id = e.course_id
            GROUP BY c.course_id
            ORDER BY c.created_at DESC";

    if ($limit) {
        $sql .= " LIMIT " . intval($limit);
    }

    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// ฟังก์ชันดึงคอร์สแนะนำ
function get_featured_courses($limit = 4)
{
    global $conn;

    $sql = "SELECT c.*, cat.category_name, cat.icon,
            CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
            COALESCE(AVG(r.rating), 0) as avg_rating,
            COUNT(DISTINCT e.enrollment_id) as total_students
            FROM courses c
            LEFT JOIN categories cat ON c.category_id = cat.category_id
            LEFT JOIN users u ON c.instructor_id = u.user_id
            LEFT JOIN reviews r ON c.course_id = r.course_id AND r.status = 'approved'
            LEFT JOIN enrollments e ON c.course_id = e.course_id
            WHERE c.status = 'published' AND (c.featured = 1 OR c.bestseller = 1)
            GROUP BY c.course_id
            ORDER BY c.featured DESC, c.bestseller DESC, c.created_at DESC
            LIMIT " . intval($limit);

    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// ฟังก์ชันดึงคอร์สขายดี
function get_bestseller_courses($limit = 4)
{
    global $conn;

    $sql = "SELECT c.*, cat.category_name, cat.icon,
            CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
            COALESCE(AVG(r.rating), 0) as avg_rating,
            COUNT(DISTINCT e.enrollment_id) as total_students
            FROM courses c
            LEFT JOIN categories cat ON c.category_id = cat.category_id
            LEFT JOIN users u ON c.instructor_id = u.user_id
            LEFT JOIN reviews r ON c.course_id = r.course_id AND r.status = 'approved'
            LEFT JOIN enrollments e ON c.course_id = e.course_id
            WHERE c.status = 'published' AND c.bestseller = 1
            GROUP BY c.course_id
            ORDER BY c.created_at DESC
            LIMIT " . intval($limit);

    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// ฟังก์ชันดึงคอร์สตาม ID (สำหรับหน้าบ้าน - เฉพาะ published)
function get_course_by_id($course_id)
{
    global $conn;

    $course_id = intval($course_id);

    $sql = "SELECT c.*, cat.category_name, cat.icon,
            CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
            u.profile_image as instructor_image,
            COALESCE(AVG(r.rating), 0) as avg_rating,
            COUNT(DISTINCT r.review_id) as total_reviews,
            COUNT(DISTINCT e.enrollment_id) as total_students
            FROM courses c
            LEFT JOIN categories cat ON c.category_id = cat.category_id
            LEFT JOIN users u ON c.instructor_id = u.user_id
            LEFT JOIN reviews r ON c.course_id = r.course_id AND r.status = 'approved'
            LEFT JOIN enrollments e ON c.course_id = e.course_id
            WHERE c.course_id = $course_id AND c.status = 'published'
            GROUP BY c.course_id";

    $result = $conn->query($sql);
    return $result ? $result->fetch_assoc() : null;
}

// ฟังก์ชันดึงคอร์สตาม ID (สำหรับหลังบ้าน - ทุก status)
function get_course_by_id_admin($course_id)
{
    global $conn;

    $course_id = intval($course_id);

    $sql = "SELECT c.*, cat.category_name, cat.icon,
            CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
            u.profile_image as instructor_image,
            COALESCE(AVG(r.rating), 0) as avg_rating,
            COUNT(DISTINCT r.review_id) as total_reviews,
            COUNT(DISTINCT e.enrollment_id) as total_students
            FROM courses c
            LEFT JOIN categories cat ON c.category_id = cat.category_id
            LEFT JOIN users u ON c.instructor_id = u.user_id
            LEFT JOIN reviews r ON c.course_id = r.course_id AND r.status = 'approved'
            LEFT JOIN enrollments e ON c.course_id = e.course_id
            WHERE c.course_id = $course_id
            GROUP BY c.course_id";

    $result = $conn->query($sql);
    return $result ? $result->fetch_assoc() : null;
}

// ฟังก์ชันดึงคอร์สตามหมวดหมู่
function get_courses_by_category($category_slug, $limit = null)
{
    global $conn;

    $category_slug = $conn->real_escape_string($category_slug);

    $sql = "SELECT c.*, cat.category_name, cat.icon,
            CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
            COALESCE(AVG(r.rating), 0) as avg_rating,
            COUNT(DISTINCT e.enrollment_id) as total_students
            FROM courses c
            INNER JOIN categories cat ON c.category_id = cat.category_id
            LEFT JOIN users u ON c.instructor_id = u.user_id
            LEFT JOIN reviews r ON c.course_id = r.course_id AND r.status = 'approved'
            LEFT JOIN enrollments e ON c.course_id = e.course_id
            WHERE c.status = 'published' AND cat.category_slug = '$category_slug'
            GROUP BY c.course_id
            ORDER BY c.created_at DESC";

    if ($limit) {
        $sql .= " LIMIT " . intval($limit);
    }

    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}
?>
