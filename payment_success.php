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

if (!$order || $order['payment_status'] != 'completed') {
    header('Location: index.php');
    exit;
}

// ดึงรายการคอร์สในคำสั่งซื้อ
$items_sql = "SELECT oi.*, c.course_title, c.thumbnail 
              FROM order_items oi
              JOIN courses c ON oi.course_id = c.course_id
              WHERE oi.order_id = " . $order['order_id'];
$order_items = $conn->query($items_sql)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ชำระเงินสำเร็จ - DevShop</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/payment-success.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="success-container">
        <div class="success-box">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>

            <h1>ชำระเงินสำเร็จ!</h1>
            <p>ขอบคุณที่ซื้อคอร์สกับเรา คุณสามารถเข้าเรียนได้ทันที</p>

            <div class="order-details">
                <div class="detail-row">
                    <span class="detail-label">หมายเลขคำสั่งซื้อ</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['order_number']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">วันที่สั่งซื้อ</span>
                    <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">วิธีการชำระเงิน</span>
                    <span class="detail-value">
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
                <div class="detail-row">
                    <span class="detail-label">ยอดรวมทั้งหมด</span>
                    <span class="detail-value" style="color: #28a745; font-size: 20px;">
                        ฿<?php echo number_format($order['total_amount']); ?>
                    </span>
                </div>
            </div>

            <div class="courses-list">
                <h3><i class="fas fa-graduation-cap"></i> คอร์สที่ซื้อ (<?php echo count($order_items); ?> คอร์ส)</h3>
                <?php foreach ($order_items as $item): ?>
                    <div class="course-item">
                        <img src="<?php echo htmlspecialchars($item['thumbnail']); ?>" 
                             alt="<?php echo htmlspecialchars($item['course_title']); ?>" 
                             class="course-thumb">
                        <div class="course-info">
                            <h4><?php echo htmlspecialchars($item['course_title']); ?></h4>
                            <div class="course-price">฿<?php echo number_format($item['price']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="action-buttons">
                <?php if (count($order_items) == 1): ?>
                    <a href="course_learn.php?id=<?php echo $order_items[0]['course_id']; ?>" class="btn btn-primary">
                        <i class="fas fa-play-circle"></i> เริ่มเรียนเลย
                    </a>
                <?php else: ?>
                    <a href="my_courses.php" class="btn btn-primary">
                        <i class="fas fa-graduation-cap"></i> ดูคอร์สทั้งหมด
                    </a>
                <?php endif; ?>
                <a href="courses.php" class="btn btn-secondary">
                    <i class="fas fa-search"></i> เลือกดูคอร์สเพิ่ม
                </a>
            </div>

            <p style="margin-top: 30px; font-size: 14px; color: #95a5a6;">
                <i class="fas fa-envelope"></i> 
                เราได้ส่งใบเสร็จไปที่อีเมล <?php echo htmlspecialchars($order['billing_email']); ?> แล้ว
            </p>
        </div>
    </div>

    <script>
        // สร้าง confetti effect
        function createConfetti() {
            const colors = ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#43e97b'];
            for (let i = 0; i < 50; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('div');
                    confetti.className = 'confetti';
                    confetti.style.left = Math.random() * 100 + '%';
                    confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.animationDelay = Math.random() * 3 + 's';
                    document.body.appendChild(confetti);
                    
                    setTimeout(() => confetti.remove(), 3000);
                }, i * 30);
            }
        }

        // เรียก confetti เมื่อโหลดหน้า
        window.addEventListener('load', createConfetti);
    </script>

    <?php include 'includes/footer.php'; ?>
</body>

</html>
