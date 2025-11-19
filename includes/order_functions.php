<?php
// ฟังก์ชันเกี่ยวกับคำสั่งซื้อ

// ฟังก์ชันสร้างคำสั่งซื้อ
function create_order($user_id, $cart_items, $payment_method, $billing_info)
{
    global $conn;

    // คำนวณราคารวม
    $total_amount = 0;
    foreach ($cart_items as $item) {
        $price = $item['discount_price'] ? $item['discount_price'] : $item['price'];
        $total_amount += $price;
    }

    // สร้างหมายเลขคำสั่งซื้อ
    $order_number = 'ORD' . time() . rand(1000, 9999);

    // เตรียมข้อมูล
    $billing_name = clean_input($billing_info['name']);
    $billing_email = clean_input($billing_info['email']);
    $billing_phone = clean_input($billing_info['phone']);
    $billing_address = clean_input($billing_info['address']);
    $payment_method = clean_input($payment_method);

    // สร้างคำสั่งซื้อ
    $order_sql = "INSERT INTO orders (
        user_id, order_number, total_amount, final_amount, payment_method, 
        payment_status, billing_name, billing_email, billing_phone, billing_address
    ) VALUES (
        $user_id, '$order_number', $total_amount, $total_amount, '$payment_method',
        'pending', '$billing_name', '$billing_email', '$billing_phone', '$billing_address'
    )";

    if ($conn->query($order_sql)) {
        $order_id = $conn->insert_id;

        // เพิ่มรายการคอร์สในคำสั่งซื้อ
        foreach ($cart_items as $item) {
            $course_id = $item['course_id'];
            $price = $item['discount_price'] ? $item['discount_price'] : $item['price'];

            $item_sql = "INSERT INTO order_items (order_id, course_id, price) 
                        VALUES ($order_id, $course_id, $price)";
            $conn->query($item_sql);
        }

        return [
            'success' => true,
            'order_id' => $order_id,
            'order_number' => $order_number
        ];
    }

    return ['success' => false, 'error' => $conn->error];
}

// ฟังก์ชันดึงข้อมูลคำสั่งซื้อ
function get_order_by_number($order_number, $user_id = null)
{
    global $conn;

    $order_number = clean_input($order_number);
    $sql = "SELECT o.*, 
            (SELECT COUNT(*) FROM order_items WHERE order_id = o.order_id) as item_count
            FROM orders o 
            WHERE o.order_number = '$order_number'";

    if ($user_id) {
        $sql .= " AND o.user_id = $user_id";
    }

    $result = $conn->query($sql);
    return $result ? $result->fetch_assoc() : null;
}

// ฟังก์ชันดึงรายการคอร์สในคำสั่งซื้อ
function get_order_items($order_id)
{
    global $conn;

    $order_id = intval($order_id);
    $sql = "SELECT oi.*, c.course_title, c.thumbnail 
            FROM order_items oi
            JOIN courses c ON oi.course_id = c.course_id
            WHERE oi.order_id = $order_id";

    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// ฟังก์ชันอัพเดทสถานะการชำระเงิน
function update_payment_status($order_id, $status)
{
    global $conn;

    $order_id = intval($order_id);
    $status = clean_input($status);

    $sql = "UPDATE orders SET payment_status = '$status'";

    if ($status == 'completed') {
        $sql .= ", paid_at = NOW()";
    }

    $sql .= " WHERE order_id = $order_id";

    return $conn->query($sql);
}

// ฟังก์ชันเพิ่มคอร์สให้ผู้ใช้หลังชำระเงิน
function enroll_courses($user_id, $order_id)
{
    global $conn;

    $user_id = intval($user_id);
    $order_id = intval($order_id);

    // ดึงรายการคอร์สในคำสั่งซื้อ
    $items = get_order_items($order_id);

    foreach ($items as $item) {
        $course_id = $item['course_id'];

        // ตรวจสอบว่ามีการลงทะเบียนแล้วหรือไม่
        $check = $conn->query("SELECT * FROM enrollments WHERE user_id = $user_id AND course_id = $course_id");

        if ($check->num_rows == 0) {
            $conn->query("INSERT INTO enrollments (user_id, course_id, order_id) VALUES ($user_id, $course_id, $order_id)");
        }
    }

    return true;
}

// ฟังก์ชันดึงประวัติคำสั่งซื้อของผู้ใช้
function get_user_orders($user_id, $limit = null)
{
    global $conn;

    $user_id = intval($user_id);
    $sql = "SELECT o.*, 
            (SELECT COUNT(*) FROM order_items WHERE order_id = o.order_id) as item_count
            FROM orders o 
            WHERE o.user_id = $user_id
            ORDER BY o.created_at DESC";

    if ($limit) {
        $sql .= " LIMIT " . intval($limit);
    }

    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// ฟังก์ชันตรวจสอบว่าผู้ใช้ซื้อคอร์สแล้วหรือไม่
function is_course_purchased($user_id, $course_id)
{
    global $conn;

    $user_id = intval($user_id);
    $course_id = intval($course_id);

    $sql = "SELECT * FROM enrollments WHERE user_id = $user_id AND course_id = $course_id";
    $result = $conn->query($sql);

    return $result && $result->num_rows > 0;
}

// ฟังก์ชันดึงสถิติคำสั่งซื้อ
function get_order_stats($user_id = null)
{
    global $conn;

    $where = $user_id ? "WHERE user_id = " . intval($user_id) : "";

    $stats = [
        'total_orders' => 0,
        'total_amount' => 0,
        'pending_orders' => 0,
        'paid_orders' => 0
    ];

    // จำนวนคำสั่งซื้อทั้งหมด
    $result = $conn->query("SELECT COUNT(*) as count FROM orders $where");
    if ($result) {
        $stats['total_orders'] = $result->fetch_assoc()['count'];
    }

    // ยอดรวมทั้งหมด
    $result = $conn->query("SELECT SUM(final_amount) as total FROM orders $where AND payment_status = 'completed'");
    if ($result) {
        $row = $result->fetch_assoc();
        $stats['total_amount'] = $row['total'] ?? 0;
    }

    // คำสั่งซื้อที่รอชำระ
    $result = $conn->query("SELECT COUNT(*) as count FROM orders $where AND payment_status = 'pending'");
    if ($result) {
        $stats['pending_orders'] = $result->fetch_assoc()['count'];
    }

    // คำสั่งซื้อที่ชำระแล้ว
    $result = $conn->query("SELECT COUNT(*) as count FROM orders $where AND payment_status = 'completed'");
    if ($result) {
        $stats['paid_orders'] = $result->fetch_assoc()['count'];
    }

    return $stats;
}
?>
