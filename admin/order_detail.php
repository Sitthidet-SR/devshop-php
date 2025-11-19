<?php
session_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/order_functions.php';
require_once 'includes/auth_check.php';

$page_title = 'รายละเอียดคำสั่งซื้อ';

// ตรวจสอบ order ID
if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$order_id = intval($_GET['id']);

// ดึงข้อมูลคำสั่งซื้อ
$order = $conn->query("
    SELECT o.*, 
    CONCAT(u.first_name, ' ', u.last_name) as customer_name,
    u.email as customer_email,
    u.phone as customer_phone
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_id = $order_id
")->fetch_assoc();

if (!$order) {
    header('Location: orders.php');
    exit;
}

// ดึงรายการคอร์สในคำสั่งซื้อ
$order_items = $conn->query("
    SELECT oi.*, c.course_title, c.thumbnail,
    CONCAT(u.first_name, ' ', u.last_name) as instructor_name
    FROM order_items oi
    JOIN courses c ON oi.course_id = c.course_id
    LEFT JOIN users u ON c.instructor_id = u.user_id
    WHERE oi.order_id = $order_id
")->fetch_all(MYSQLI_ASSOC);
?>

<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="../assets/css/admin-reviews.css">

<style>
    .order-detail-card {
        background: white;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 20px;
        border-bottom: 2px solid #ecf0f1;
        margin-bottom: 20px;
    }

    .order-number {
        font-size: 24px;
        font-weight: bold;
        color: #2c3e50;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .info-item {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 5px;
    }

    .info-label {
        display: block;
        font-size: 13px;
        color: #7f8c8d;
        margin-bottom: 5px;
    }

    .info-value {
        display: block;
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
    }

    .section-title {
        font-size: 18px;
        color: #2c3e50;
        margin: 25px 0 15px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid #ecf0f1;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }

    .items-table th {
        background: #f8f9fa;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #2c3e50;
        border-bottom: 2px solid #ecf0f1;
    }

    .items-table td {
        padding: 12px;
        border-bottom: 1px solid #ecf0f1;
    }

    .course-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .course-thumbnail {
        width: 80px;
        height: 60px;
        object-fit: cover;
        border-radius: 5px;
    }

    .course-details h4 {
        margin: 0 0 5px 0;
        color: #2c3e50;
        font-size: 15px;
    }

    .course-details small {
        color: #7f8c8d;
    }

    .summary-box {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        font-size: 16px;
    }

    .summary-total {
        font-size: 20px;
        font-weight: bold;
        color: #2c3e50;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }

    .btn-primary {
        background: #667eea;
        color: white;
    }

    .btn-primary:hover {
        background: #5568d3;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
    }

    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-completed {
        background: #d4edda;
        color: #155724;
    }

    .status-failed {
        background: #f8d7da;
        color: #721c24;
    }

    .timeline {
        margin: 20px 0;
    }

    .timeline-item {
        display: flex;
        gap: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 5px;
        margin-bottom: 10px;
    }

    .timeline-date {
        font-weight: 600;
        color: #667eea;
        min-width: 150px;
    }

    .timeline-content {
        color: #2c3e50;
    }
</style>

<div class="content-box">
    <a href="orders.php" class="btn-sm btn-secondary" style="margin-bottom: 20px;">
        <i class="fas fa-arrow-left"></i> กลับไปรายการคำสั่งซื้อ
    </a>

    <div class="order-detail-card">
        <div class="order-header">
            <div>
                <div class="order-number">
                    <i class="fas fa-hashtag"></i> <?php echo htmlspecialchars($order['order_number']); ?>
                </div>
                <small style="color: #7f8c8d;">
                    สั่งซื้อเมื่อ <?php echo date('d/m/Y H:i น.', strtotime($order['created_at'])); ?>
                </small>
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

        <!-- Customer Information -->
        <h2 class="section-title">
            <i class="fas fa-user"></i> ข้อมูลลูกค้า
        </h2>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">ชื่อ-นามสกุล</span>
                <span class="info-value"><?php echo htmlspecialchars($order['customer_name']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">อีเมล</span>
                <span class="info-value"><?php echo htmlspecialchars($order['customer_email']); ?></span>
            </div>
            <?php if ($order['billing_phone']): ?>
                <div class="info-item">
                    <span class="info-label">เบอร์โทรศัพท์</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['billing_phone']); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Payment Information -->
        <h2 class="section-title">
            <i class="fas fa-credit-card"></i> ข้อมูลการชำระเงิน
        </h2>
        <div class="info-grid">
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
            <div class="info-item">
                <span class="info-label">สถานะการชำระเงิน</span>
                <span class="info-value"><?php echo $status_text[$order['payment_status']]; ?></span>
            </div>
            <?php if ($order['paid_at']): ?>
                <div class="info-item">
                    <span class="info-label">วันที่ชำระเงิน</span>
                    <span class="info-value"><?php echo date('d/m/Y H:i น.', strtotime($order['paid_at'])); ?></span>
                </div>
            <?php endif; ?>
            <?php if ($order['transaction_id']): ?>
                <div class="info-item">
                    <span class="info-label">Transaction ID</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['transaction_id']); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Order Items -->
        <h2 class="section-title">
            <i class="fas fa-shopping-bag"></i> รายการคอร์ส
        </h2>
        <table class="items-table">
            <thead>
                <tr>
                    <th>คอร์ส</th>
                    <th>ผู้สอน</th>
                    <th style="text-align: right;">ราคา</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td>
                            <div class="course-info">
                                <img src="../<?php echo htmlspecialchars($item['thumbnail']); ?>"
                                    alt="<?php echo htmlspecialchars($item['course_title']); ?>"
                                    class="course-thumbnail">
                                <div class="course-details">
                                    <h4><?php echo htmlspecialchars($item['course_title']); ?></h4>
                                    <small><i class="fas fa-infinity"></i> เข้าถึงตลอดชีพ</small>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($item['instructor_name']); ?></td>
                        <td style="text-align: right; font-weight: 600;">
                            ฿<?php echo number_format($item['price']); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

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
            <div class="summary-row" style="border-top: 2px solid #ddd; padding-top: 15px; margin-top: 10px;">
                <span class="summary-total">ยอดรวมทั้งหมด</span>
                <span class="summary-total" style="color: #667eea;">฿<?php echo number_format($order['final_amount']); ?></span>
            </div>
        </div>

        <!-- Timeline -->
        <h2 class="section-title">
            <i class="fas fa-history"></i> ประวัติการทำรายการ
        </h2>
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-date">
                    <?php echo date('d/m/Y H:i น.', strtotime($order['created_at'])); ?>
                </div>
                <div class="timeline-content">
                    <i class="fas fa-shopping-cart"></i> สร้างคำสั่งซื้อ
                </div>
            </div>
            <?php if ($order['paid_at']): ?>
                <div class="timeline-item">
                    <div class="timeline-date">
                        <?php echo date('d/m/Y H:i น.', strtotime($order['paid_at'])); ?>
                    </div>
                    <div class="timeline-content">
                        <i class="fas fa-check-circle"></i> ชำระเงินสำเร็จ
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($order['updated_at'] != $order['created_at']): ?>
                <div class="timeline-item">
                    <div class="timeline-date">
                        <?php echo date('d/m/Y H:i น.', strtotime($order['updated_at'])); ?>
                    </div>
                    <div class="timeline-content">
                        <i class="fas fa-sync"></i> อัพเดทข้อมูล
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="orders.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> กลับไปรายการคำสั่งซื้อ
            </a>
            <a href="javascript:void(0)"
                class="btn btn-primary"
                onclick="contactCustomer('<?php echo htmlspecialchars($order['customer_email']); ?>', '<?php echo htmlspecialchars($order['customer_name']); ?>', '<?php echo htmlspecialchars($order['order_number']); ?>')">
                <i class="fas fa-envelope"></i> ติดต่อลูกค้า
            </a>
        </div>
    </div>
</div>

<script>
    function contactCustomer(email, name, orderNumber) {
        // สร้าง Gmail compose URL
        const subject = `เกี่ยวกับคำสั่งซื้อ ${orderNumber}`;
        const body = `สวัสดีครับคุณ ${name},%0D%0A%0D%0A`;
        const gmailUrl = `https://mail.google.com/mail/?view=cm&fs=1&to=${encodeURIComponent(email)}&su=${encodeURIComponent(subject)}&body=${body}`;

        // เปิด Gmail ในแท็บใหม่
        window.open(gmailUrl, '_blank');
    }
</script>

<?php include 'includes/footer.php'; ?>