<?php
// ฟังก์ชันเกี่ยวกับตะกร้าสินค้าและรายการถูกใจ

// ฟังก์ชันนับจำนวนสินค้าในตะกร้า
function get_cart_count($user_id = null)
{
    global $conn;

    if (!$user_id) {
        // ถ้าไม่ได้ login ให้ดูจาก session
        return isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
    }

    $user_id = intval($user_id);
    $sql = "SELECT COUNT(*) as count FROM cart WHERE user_id = $user_id";
    $result = $conn->query($sql);

    if ($result) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    return 0;
}

// ฟังก์ชันนับจำนวนรายการถูกใจ
function get_wishlist_count($user_id = null)
{
    global $conn;

    if (!$user_id) {
        // ถ้าไม่ได้ login ให้ดูจาก session
        return isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0;
    }

    $user_id = intval($user_id);
    $sql = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = $user_id";
    $result = $conn->query($sql);

    if ($result) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    return 0;
}

// ฟังก์ชันดึงรายการในตะกร้า
function get_cart_items($user_id)
{
    global $conn;

    $user_id = intval($user_id);

    $sql = "SELECT c.*, co.course_title, co.thumbnail, co.price, co.discount_price
            FROM cart c
            INNER JOIN courses co ON c.course_id = co.course_id
            WHERE c.user_id = $user_id
            ORDER BY c.added_at DESC";

    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// ฟังก์ชันดึงรายการถูกใจ
function get_wishlist_items($user_id)
{
    global $conn;

    $user_id = intval($user_id);

    $sql = "SELECT w.*, co.course_title, co.thumbnail, co.price, co.discount_price,
            CONCAT(u.first_name, ' ', u.last_name) as instructor_name
            FROM wishlist w
            INNER JOIN courses co ON w.course_id = co.course_id
            LEFT JOIN users u ON co.instructor_id = u.user_id
            WHERE w.user_id = $user_id
            ORDER BY w.added_at DESC";

    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}
?>
