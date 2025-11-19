<?php
// ฟังก์ชันเกี่ยวกับหมวดหมู่

// ฟังก์ชันดึงหมวดหมู่ทั้งหมด
function get_all_categories()
{
    global $conn;

    $sql = "SELECT cat.*, COUNT(c.course_id) as course_count
            FROM categories cat
            LEFT JOIN courses c ON cat.category_id = c.category_id AND c.status = 'published'
            WHERE cat.status = 'active'
            GROUP BY cat.category_id
            ORDER BY cat.display_order ASC, cat.category_name ASC";

    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// ฟังก์ชันดึงหมวดหมู่ตาม slug
function get_category_by_slug($slug)
{
    global $conn;

    $slug = $conn->real_escape_string($slug);

    $sql = "SELECT cat.*, COUNT(c.course_id) as course_count
            FROM categories cat
            LEFT JOIN courses c ON cat.category_id = c.category_id AND c.status = 'published'
            WHERE cat.category_slug = '$slug' AND cat.status = 'active'
            GROUP BY cat.category_id";

    $result = $conn->query($sql);
    return $result ? $result->fetch_assoc() : null;
}

// ฟังก์ชันดึงหมวดหมู่ตาม ID
function get_category_by_id($category_id)
{
    global $conn;

    $category_id = intval($category_id);

    $sql = "SELECT cat.*, COUNT(c.course_id) as course_count
            FROM categories cat
            LEFT JOIN courses c ON cat.category_id = c.category_id AND c.status = 'published'
            WHERE cat.category_id = $category_id AND cat.status = 'active'
            GROUP BY cat.category_id";

    $result = $conn->query($sql);
    return $result ? $result->fetch_assoc() : null;
}
?>
