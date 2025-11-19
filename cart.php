<?php
session_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/cart_functions.php';
require_once 'includes/course_functions.php';
require_once 'includes/auth_functions.php';

// ตรวจสอบว่า login หรือไม่
if (!is_logged_in()) {
    header('Location: login.php?redirect=cart.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// ลบสินค้าออกจากตะกร้า
if (isset($_GET['remove'])) {
    $course_id = intval($_GET['remove']);
    $sql = "DELETE FROM cart WHERE user_id = $user_id AND course_id = $course_id";
    if ($conn->query($sql)) {
        require_once 'includes/redirect_helper.php';
        redirect_self_with_message('removed_from_cart');
    }
}

// ดึงรายการในตะกร้า
$cart_items = get_cart_items($user_id);

// คำนวณราคารวม
$total_price = 0;
$total_discount = 0;
foreach ($cart_items as $item) {
    $price = $item['discount_price'] ?: $item['price'];
    $total_price += $price;
    if ($item['discount_price']) {
        $total_discount += ($item['price'] - $item['discount_price']);
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตะกร้าสินค้า - DevShop</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/sweetalert-helper.js?v=<?php echo time(); ?>"></script>
    <link rel="stylesheet" href="assets/css/cart.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="cart-container">
        <div class="page-header">
            <h1><i class="fas fa-shopping-cart"></i> ตะกร้าสินค้า</h1>
        </div>



        <?php if (empty($cart_items)): ?>
            <div class="cart-items">
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h2>ตะกร้าสินค้าว่างเปล่า</h2>
                    <p>คุณยังไม่มีคอร์สในตะกร้า</p>
                    <a href="courses.php" class="btn-browse">เลือกดูคอร์ส</a>
                </div>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <img src="<?php echo htmlspecialchars($item['thumbnail']); ?>"
                                alt="<?php echo htmlspecialchars($item['course_title']); ?>"
                                class="item-image">
                            <div class="item-details">
                                <h3 class="item-title"><?php echo htmlspecialchars($item['course_title']); ?></h3>
                                <div class="item-price">
                                    <?php if ($item['discount_price']): ?>
                                        <span class="current-price">฿<?php echo format_price($item['discount_price']); ?></span>
                                        <span class="original-price">฿<?php echo format_price($item['price']); ?></span>
                                    <?php else: ?>
                                        <span class="current-price">฿<?php echo format_price($item['price']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <a href="javascript:void(0)"
                                    class="btn-remove"
                                    onclick="confirmDelete('คุณต้องการลบคอร์สนี้ออกจากตะกร้าหรือไม่?', '?remove=<?php echo $item['course_id']; ?>')">
                                    <i class="fas fa-trash"></i> ลบ
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <h3 class="summary-title">สรุปคำสั่งซื้อ</h3>
                    <div class="summary-row">
                        <span>จำนวนคอร์ส:</span>
                        <span><?php echo count($cart_items); ?> คอร์ส</span>
                    </div>
                    <?php if ($total_discount > 0): ?>
                        <div class="summary-row">
                            <span>ส่วนลด:</span>
                            <span style="color: #e74c3c;">-฿<?php echo format_price($total_discount); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="summary-total">
                        <span>ยอดรวมทั้งหมด:</span>
                        <span>฿<?php echo format_price($total_price); ?></span>
                    </div>
                    <a href="checkout.php" class="btn-checkout" style="text-align: center; text-decoration: none; display: block;">
                        <i class="fas fa-credit-card"></i> ดำเนินการชำระเงิน
                    </a>
                    <a href="courses.php" style="display: block; text-align: center; margin-top: 15px; color: #667eea; text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> เลือกดูคอร์สเพิ่มเติม
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>