<?php
session_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth_functions.php';
require_once 'includes/order_functions.php';

// ตรวจสอบว่า login หรือไม่
if (!is_logged_in()) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$user_id = $_SESSION['user_id'];

// ตรวจสอบ order number
if (!isset($_GET['order'])) {
    header('Location: order_history.php');
    exit;
}

$order_number = clean_input($_GET['order']);

// ดึงข้อมูลคำสั่งซื้อ
$order = get_order_by_number($order_number, $user_id);

if (!$order) {
    header('Location: order_history.php');
    exit;
}

// ดึงรายการคอร์สในคำสั่งซื้อ
$order_items = get_order_items($order['order_id']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดคำสั่งซื้อ - DevShop</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/order-detail.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="order-detail-container">
        <a href="order_history.php" class="back-link">
            <i class="fas fa-arrow-left"></i> กลับไปประวัติการสั่งซื้อ
        </a>

        <div class="order-card">
            <div class="order-header">
                <div>
                    <h1 class="order-title">รายละเอียดคำสั่งซื้อ</h1>
                    <div class="order-number">
                        <i class="fas fa-hashtag"></i> <?php echo htmlspecialchars($order['order_number']); ?>
                    </div>
                </div>
                <div>
                    <?php
                    $status_class = 'status-' . $order['payment_status'];
                    $status_text = [
                        'pending' => 'รอชำระเงิน',
                        'completed' => 'ชำระเงินแล้ว',
                        'failed' => 'ชำระเงินล้มเหลว',
                        'refunded' => 'คืนเงินแล้ว'
                    ];
                    ?>
                    <span class="status-badge <?php echo $status_class; ?>">
                        <?php echo $status_text[$order['payment_status']]; ?>
                    </span>
                </div>
            </div>

            <!-- Order Information -->
            <div class="info-section">
                <h2 class="section-title">
                    <i class="fas fa-info-circle"></i> ข้อมูลคำสั่งซื้อ
                </h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">วันที่สั่งซื้อ</span>
                        <span class="info-value">
                            <?php echo date('d/m/Y H:i น.', strtotime($order['created_at'])); ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">วิธีการชำระเงิน</span>
                        <span class="info-value">
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
                    <?php if ($order['paid_at']): ?>
                        <div class="info-item">
                            <span class="info-label">วันที่ชำระเงิน</span>
                            <span class="info-value">
                                <?php echo date('d/m/Y H:i น.', strtotime($order['paid_at'])); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <div class="info-item">
                        <span class="info-label">จำนวนคอร์ส</span>
                        <span class="info-value"><?php echo $order['item_count']; ?> คอร์ส</span>
                    </div>
                </div>
            </div>

            <!-- Billing Information -->
            <?php if ($order['billing_name']): ?>
                <div class="info-section">
                    <h2 class="section-title">
                        <i class="fas fa-user"></i> ข้อมูลผู้ซื้อ
                    </h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">ชื่อ-นามสกุล</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['billing_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">อีเมล</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['billing_email']); ?></span>
                        </div>
                        <?php if ($order['billing_phone']): ?>
                            <div class="info-item">
                                <span class="info-label">เบอร์โทรศัพท์</span>
                                <span class="info-value"><?php echo htmlspecialchars($order['billing_phone']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($order['billing_address']): ?>
                            <div class="info-item">
                                <span class="info-label">ที่อยู่</span>
                                <span class="info-value"><?php echo nl2br(htmlspecialchars($order['billing_address'])); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Order Items -->
            <div class="info-section">
                <h2 class="section-title">
                    <i class="fas fa-shopping-bag"></i> รายการคอร์ส
                </h2>
                <div class="items-list">
                    <?php foreach ($order_items as $item): ?>
                        <div class="item-row">
                            <img src="<?php echo htmlspecialchars($item['thumbnail']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['course_title']); ?>" 
                                 class="item-image">
                            <div class="item-details">
                                <div class="item-title"><?php echo htmlspecialchars($item['course_title']); ?></div>
                                <div class="item-meta">
                                    <span><i class="fas fa-tag"></i> คอร์สออนไลน์</span>
                                    <span><i class="fas fa-infinity"></i> เข้าถึงตลอดชีพ</span>
                                </div>
                            </div>
                            <div class="item-price">
                                ฿<?php echo number_format($item['price']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="summary-box">
                <div class="summary-row">
                    <span>ราคารวม</span>
                    <span>฿<?php echo number_format($order['total_amount']); ?></span>
                </div>
                <?php if ($order['discount_amount'] > 0): ?>
                    <div class="summary-row">
                        <span>ส่วนลด</span>
                        <span style="color: #e74c3c;">-฿<?php echo number_format($order['discount_amount']); ?></span>
                    </div>
                <?php endif; ?>
                <div class="summary-row">
                    <span class="summary-total">ยอดรวมทั้งหมด</span>
                    <span class="summary-total">฿<?php echo number_format($order['final_amount']); ?></span>
                </div>
            </div>

            <!-- Timeline -->
            <div class="info-section">
                <h2 class="section-title">
                    <i class="fas fa-history"></i> ประวัติการทำรายการ
                </h2>
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-date">
                            <?php echo date('d/m/Y H:i น.', strtotime($order['created_at'])); ?>
                        </div>
                        <div class="timeline-content">สร้างคำสั่งซื้อ</div>
                    </div>
                    <?php if ($order['paid_at']): ?>
                        <div class="timeline-item">
                            <div class="timeline-date">
                                <?php echo date('d/m/Y H:i น.', strtotime($order['paid_at'])); ?>
                            </div>
                            <div class="timeline-content">ชำระเงินสำเร็จ</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <?php if ($order['payment_status'] == 'completed'): ?>
                    <?php if (count($order_items) == 1): ?>
                        <a href="course_learn.php?id=<?php echo $order_items[0]['course_id']; ?>" class="btn btn-primary">
                            <i class="fas fa-play-circle"></i> เข้าเรียนคอร์ส
                        </a>
                    <?php else: ?>
                        <a href="my_courses.php" class="btn btn-primary">
                            <i class="fas fa-graduation-cap"></i> ดูคอร์สทั้งหมด
                        </a>
                    <?php endif; ?>
                    <a href="order_history.php" class="btn btn-secondary">
                        <i class="fas fa-list"></i> ดูคำสั่งซื้ออื่น
                    </a>
                <?php elseif ($order['payment_status'] == 'pending'): ?>
                    <a href="payment.php?order=<?php echo $order['order_number']; ?>" class="btn btn-primary">
                        <i class="fas fa-credit-card"></i> ชำระเงิน
                    </a>
                    <a href="order_history.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> ยกเลิก
                    </a>
                <?php else: ?>
                    <a href="order_history.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> กลับไปประวัติการสั่งซื้อ
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>
