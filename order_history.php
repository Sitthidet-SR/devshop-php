<?php
session_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth_functions.php';
require_once 'includes/order_functions.php';

// ตรวจสอบว่า login หรือไม่
if (!is_logged_in()) {
    header('Location: login.php?redirect=order_history.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// ดึงประวัติคำสั่งซื้อ
$orders = get_user_orders($user_id);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการสั่งซื้อ - DevShop</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/order-history.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="order-history-container">
        <div class="page-header">
            <h1><i class="fas fa-receipt"></i> ประวัติการสั่งซื้อ</h1>
            <p style="color: #7f8c8d;">รายการคำสั่งซื้อทั้งหมดของคุณ</p>
        </div>

        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
                <?php $order_items = get_order_items($order['order_id']); ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-number">
                                <i class="fas fa-shopping-bag"></i> 
                                คำสั่งซื้อ: <?php echo htmlspecialchars($order['order_number']); ?>
                            </div>
                            <div class="order-date">
                                <i class="fas fa-calendar"></i> 
                                <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
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
                            <span class="order-status <?php echo $status_class; ?>">
                                <?php echo $status_text[$order['payment_status']]; ?>
                            </span>
                        </div>
                    </div>

                    <div class="order-body">
                        <div class="order-items">
                            <?php foreach ($order_items as $item): ?>
                                <div class="order-item">
                                    <img src="<?php echo htmlspecialchars($item['thumbnail']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['course_title']); ?>" 
                                         class="item-thumb">
                                    <div class="item-info">
                                        <div class="item-title"><?php echo htmlspecialchars($item['course_title']); ?></div>
                                        <div class="item-price">฿<?php echo number_format($item['price']); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="order-summary">
                            <div class="summary-row">
                                <span>จำนวนคอร์ส</span>
                                <span><?php echo $order['item_count']; ?> คอร์ส</span>
                            </div>
                            <div class="summary-row">
                                <span>วิธีชำระเงิน</span>
                                <span>
                                    <?php 
                                    $methods = [
                                        'credit_card' => 'บัตรเครดิต',
                                        'promptpay' => 'พร้อมเพย์',
                                        'bank_transfer' => 'โอนเงิน'
                                    ];
                                    echo $methods[$order['payment_method']];
                                    ?>
                                </span>
                            </div>
                            <div class="summary-total">
                                <span>ยอดรวม</span>
                                <span>฿<?php echo number_format($order['total_amount']); ?></span>
                            </div>

                            <div class="order-actions">
                                <?php if ($order['payment_status'] == 'completed'): ?>
                                    <a href="my_courses.php" class="btn btn-primary">
                                        <i class="fas fa-graduation-cap"></i> ดูคอร์ส
                                    </a>
                                <?php elseif ($order['payment_status'] == 'pending'): ?>
                                    <a href="payment.php?order=<?php echo $order['order_number']; ?>" class="btn btn-primary">
                                        <i class="fas fa-credit-card"></i> ชำระเงิน
                                    </a>
                                <?php endif; ?>
                                <a href="javascript:void(0)" class="btn btn-secondary" onclick="viewOrderDetail('<?php echo $order['order_number']; ?>')">
                                    <i class="fas fa-eye"></i> รายละเอียด
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-receipt"></i>
                <h2>ยังไม่มีประวัติการสั่งซื้อ</h2>
                <p style="color: #7f8c8d; margin-bottom: 30px;">เริ่มต้นการเรียนรู้ด้วยการเลือกคอร์สที่คุณสนใจ</p>
                <a href="courses.php" class="btn btn-primary" style="display: inline-flex;">
                    <i class="fas fa-search"></i> เลือกดูคอร์ส
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function viewOrderDetail(orderNumber) {
            // สามารถเพิ่มฟังก์ชันแสดงรายละเอียดเพิ่มเติมได้
            window.location.href = 'order_detail.php?order=' + orderNumber;
        }
    </script>

    <?php include 'includes/footer.php'; ?>
</body>

</html>
