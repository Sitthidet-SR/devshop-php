<?php
session_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth_functions.php';

// ตรวจสอบว่า login หรือไม่
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// ตรวจสอบ order number
if (!isset($_GET['order'])) {
    header('Location: index.php');
    exit;
}

$order_number = clean_input($_GET['order']);

// ดึงข้อมูลคำสั่งซื้อ
$order_sql = "SELECT o.*, 
              (SELECT COUNT(*) FROM order_items WHERE order_id = o.order_id) as item_count
              FROM orders o 
              WHERE o.order_number = '$order_number' AND o.user_id = $user_id";
$order = $conn->query($order_sql)->fetch_assoc();

if (!$order) {
    header('Location: index.php');
    exit;
}

// ดึงรายการคอร์สในคำสั่งซื้อ
$items_sql = "SELECT oi.*, c.course_title, c.thumbnail 
              FROM order_items oi
              JOIN courses c ON oi.course_id = c.course_id
              WHERE oi.order_id = " . $order['order_id'];
$order_items = $conn->query($items_sql)->fetch_all(MYSQLI_ASSOC);

// จำลองการชำระเงิน (สำหรับ demo)
if (isset($_GET['confirm'])) {
    // อัพเดทสถานะเป็น completed
    $conn->query("UPDATE orders SET payment_status = 'completed', paid_at = NOW() WHERE order_id = " . $order['order_id']);
    
    // เพิ่มคอร์สให้ผู้ใช้
    foreach ($order_items as $item) {
        $course_id = $item['course_id'];
        $check = $conn->query("SELECT * FROM enrollments WHERE user_id = $user_id AND course_id = $course_id");
        
        if ($check->num_rows == 0) {
            $conn->query("INSERT INTO enrollments (user_id, course_id, order_id) VALUES ($user_id, $course_id, " . $order['order_id'] . ")");
        }
    }
    
    // Redirect โดยไม่มี msg parameter เพราะหน้า success มี UI แสดงอยู่แล้ว
    header('Location: payment_success.php?order=' . $order_number);
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ชำระเงิน - DevShop</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/payment.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="payment-container">
        <div class="payment-box">
            <div class="payment-icon">
                <?php if ($order['payment_method'] == 'promptpay'): ?>
                    <i class="fas fa-qrcode"></i>
                <?php elseif ($order['payment_method'] == 'credit_card'): ?>
                    <i class="fas fa-credit-card"></i>
                <?php else: ?>
                    <i class="fas fa-university"></i>
                <?php endif; ?>
            </div>

            <h1 style="font-size: 28px; margin-bottom: 10px;">ชำระเงิน</h1>
            <p style="color: #7f8c8d; margin-bottom: 30px;">
                คำสั่งซื้อ: <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
            </p>

            <div class="order-info">
                <div class="info-row">
                    <span>จำนวนคอร์ส</span>
                    <span><?php echo $order['item_count']; ?> คอร์ส</span>
                </div>
                <div class="info-row">
                    <span>วิธีการชำระเงิน</span>
                    <span>
                        <?php 
                        $methods = [
                            'credit_card' => 'บัตรเครดิต/เดบิต',
                            'promptpay' => 'พร้อมเพย์',
                            'bank_transfer' => 'โอนเงินผ่านธนาคาร'
                        ];
                        echo $methods[$order['payment_method']];
                        ?>
                    </span>
                </div>
                <div class="info-row">
                    <span>ยอดรวมทั้งหมด</span>
                    <span>฿<?php echo number_format($order['total_amount']); ?></span>
                </div>
            </div>

            <?php if ($order['payment_method'] == 'promptpay'): ?>
                <div class="qr-code">
                    <i class="fas fa-qrcode"></i>
                </div>
                <p style="color: #7f8c8d; margin-bottom: 20px;">
                    สแกน QR Code เพื่อชำระเงินผ่านพร้อมเพย์
                </p>
            <?php elseif ($order['payment_method'] == 'bank_transfer'): ?>
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: left;">
                    <h3 style="margin-bottom: 15px;">ข้อมูลบัญชีธนาคาร</h3>
                    <p><strong>ธนาคาร:</strong> ธนาคารกสิกรไทย</p>
                    <p><strong>ชื่อบัญชี:</strong> บริษัท DevShop จำกัด</p>
                    <p><strong>เลขที่บัญชี:</strong> 123-4-56789-0</p>
                    <p style="color: #e74c3c; margin-top: 10px;">
                        <i class="fas fa-info-circle"></i> โปรดโอนเงินภายใน 24 ชั่วโมง
                    </p>
                </div>
            <?php endif; ?>

            <p style="color: #7f8c8d; font-size: 14px; margin: 20px 0;">
                <i class="fas fa-info-circle"></i> นี่เป็นระบบ Demo - คลิกปุ่มด้านล่างเพื่อจำลองการชำระเงิน
            </p>

            <a href="?order=<?php echo $order_number; ?>&confirm=1" class="btn-confirm">
                <i class="fas fa-check"></i> ยืนยันการชำระเงิน (Demo)
            </a>
            <a href="cart.php" class="btn-cancel">
                <i class="fas fa-times"></i> ยกเลิก
            </a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>
