<?php
session_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/order_functions.php';
require_once 'includes/auth_check.php';

$page_title = 'จัดการคำสั่งซื้อ';

// ดึงคำสั่งซื้อทั้งหมด
$filter = isset($_GET['status']) ? clean_input($_GET['status']) : 'all';
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

$sql = "SELECT o.*, 
        CONCAT(u.first_name, ' ', u.last_name) as customer_name,
        u.email as customer_email,
        (SELECT COUNT(*) FROM order_items WHERE order_id = o.order_id) as item_count
        FROM orders o
        JOIN users u ON o.user_id = u.user_id
        WHERE 1=1";

if ($filter != 'all') {
    $sql .= " AND o.payment_status = '$filter'";
}

if ($search) {
    $sql .= " AND (o.order_number LIKE '%$search%' OR u.first_name LIKE '%$search%' OR u.last_name LIKE '%$search%' OR u.email LIKE '%$search%')";
}

$sql .= " ORDER BY o.created_at DESC";

$orders = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// สถิติ
$stats = [
    'all' => $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'],
    'pending' => $conn->query("SELECT COUNT(*) as count FROM orders WHERE payment_status = 'pending'")->fetch_assoc()['count'],
    'completed' => $conn->query("SELECT COUNT(*) as count FROM orders WHERE payment_status = 'completed'")->fetch_assoc()['count'],
    'failed' => $conn->query("SELECT COUNT(*) as count FROM orders WHERE payment_status = 'failed'")->fetch_assoc()['count']
];
?>

<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="../assets/css/admin-reviews.css">

<div class="content-box">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><i class="fas fa-shopping-cart"></i> คำสั่งซื้อ</h2>
    </div>

    <div class="filter-tabs" style="margin-bottom: 20px;">
        <a href="orders.php" class="filter-tab <?php echo $filter == 'all' ? 'active' : ''; ?>">
            ทั้งหมด <span class="badge"><?php echo $stats['all']; ?></span>
        </a>
        <a href="?status=pending" class="filter-tab <?php echo $filter == 'pending' ? 'active' : ''; ?>">
            <i class="fas fa-clock"></i> รอชำระ <span class="badge badge-warning"><?php echo $stats['pending']; ?></span>
        </a>
        <a href="?status=completed" class="filter-tab <?php echo $filter == 'completed' ? 'active' : ''; ?>">
            <i class="fas fa-check-circle"></i> สำเร็จ <span class="badge badge-success"><?php echo $stats['completed']; ?></span>
        </a>
        <a href="?status=failed" class="filter-tab <?php echo $filter == 'failed' ? 'active' : ''; ?>">
            <i class="fas fa-times-circle"></i> ล้มเหลว <span class="badge badge-danger"><?php echo $stats['failed']; ?></span>
        </a>
    </div>

    <!-- Search Box -->
    <div style="margin-bottom: 20px;">
        <form method="GET" style="display: flex; gap: 10px;">
            <input type="hidden" name="status" value="<?php echo $filter; ?>">
            <input type="text" name="search" placeholder="ค้นหาด้วยหมายเลขคำสั่งซื้อ, ชื่อลูกค้า, อีเมล..." 
                   value="<?php echo htmlspecialchars($search); ?>"
                   style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            <button type="submit" style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer;">
                <i class="fas fa-search"></i> ค้นหา
            </button>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="orders-table">
            <table>
                <thead>
                    <tr>
                        <th>หมายเลขคำสั่งซื้อ</th>
                        <th>ลูกค้า</th>
                        <th>จำนวนคอร์ส</th>
                        <th>ยอดรวม</th>
                        <th>วิธีชำระเงิน</th>
                        <th>สถานะ</th>
                        <th>วันที่สั่งซื้อ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #7f8c8d;">
                                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
                                ไม่พบคำสั่งซื้อ
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                <td>
                                    <?php echo htmlspecialchars($order['customer_name']); ?><br>
                                    <small style="color: #7f8c8d;"><?php echo htmlspecialchars($order['customer_email']); ?></small>
                                </td>
                                <td><?php echo $order['item_count']; ?> คอร์ส</td>
                                <td><strong style="color: #667eea; font-size: 16px;">฿<?php echo number_format($order['total_amount']); ?></strong></td>
                                <td>
                                    <?php 
                                    $methods = [
                                        'credit_card' => 'บัตรเครดิต',
                                        'promptpay' => 'พร้อมเพย์',
                                        'bank_transfer' => 'โอนเงิน'
                                    ];
                                    echo $methods[$order['payment_method']];
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $status_class = 'status-' . $order['payment_status'];
                                    $status_text = [
                                        'pending' => 'รอชำระเงิน',
                                        'completed' => 'ชำระเงินแล้ว',
                                        'failed' => 'ล้มเหลว',
                                        'refunded' => 'คืนเงินแล้ว'
                                    ];
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo $status_text[$order['payment_status']]; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <a href="order_detail.php?id=<?php echo $order['order_id']; ?>" class="btn-sm btn-success">
                                        <i class="fas fa-eye"></i> ดูรายละเอียด
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
