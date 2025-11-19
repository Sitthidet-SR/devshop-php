<?php
session_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth_functions.php';
require_once 'includes/cart_functions.php';

// ตรวจสอบว่า login หรือไม่
if (!is_logged_in()) {
    header('Location: login.php?redirect=checkout.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// ดึงรายการในตะกร้า
$cart_items = get_cart_items($user_id);

if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

// คำนวณราคารวม
$total_price = 0;
foreach ($cart_items as $item) {
    $price = $item['discount_price'] ? $item['discount_price'] : $item['price'];
    $total_price += $price;
}

// ดึงข้อมูลผู้ใช้
$user_sql = "SELECT * FROM users WHERE user_id = $user_id";
$user = $conn->query($user_sql)->fetch_assoc();

// ประมวลผลการชำระเงิน
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $payment_method = clean_input($_POST['payment_method']);
    $billing_name = clean_input($_POST['billing_name']);
    $billing_email = clean_input($_POST['billing_email']);
    $billing_phone = clean_input($_POST['billing_phone']);
    $billing_address = clean_input($_POST['billing_address']);

    // สร้างคำสั่งซื้อ
    $order_number = 'ORD' . time() . rand(1000, 9999);

    // คำนวณ final_amount (ตอนนี้ยังไม่มีส่วนลด)
    $final_amount = $total_price;

    $order_sql = "INSERT INTO orders (
        user_id, order_number, total_amount, final_amount, payment_method, 
        payment_status, billing_name, billing_email, billing_phone, billing_address
    ) VALUES (
        $user_id, '$order_number', $total_price, $final_amount, '$payment_method',
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

        // ลบสินค้าในตะกร้า
        $conn->query("DELETE FROM cart WHERE user_id = $user_id");

        header("Location: payment.php?order=$order_number");
        exit;
    } else {
        $error = 'เกิดข้อผิดพลาด: ' . $conn->error;
    }
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/sweetalert-helper.js?v=<?php echo time(); ?>"></script>
    <link rel="stylesheet" href="assets/css/checkout.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <?php if ($error): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด!',
                    text: '<?php echo addslashes($error); ?>',
                    confirmButtonColor: '#667eea',
                    confirmButtonText: 'ตกลง'
                });
            });
        </script>
    <?php endif; ?>

    <div class="checkout-container">
        <h1 style="font-size: 32px; margin-bottom: 30px;">
            <i class="fas fa-credit-card"></i> ชำระเงิน
        </h1>

        <form method="POST" id="checkout-form">
            <div class="checkout-grid">
                <!-- Left Column -->
                <div>
                    <!-- Billing Information -->
                    <div class="checkout-section">
                        <h2 class="section-title"><i class="fas fa-user"></i> ข้อมูลผู้ซื้อ</h2>
                        <div class="form-group">
                            <label>ชื่อ-นามสกุล *</label>
                            <input type="text" name="billing_name" value="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>อีเมล *</label>
                            <input type="email" name="billing_email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>เบอร์โทรศัพท์ *</label>
                            <input type="text" name="billing_phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>ที่อยู่</label>
                            <textarea name="billing_address" rows="3"></textarea>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="checkout-section" style="margin-top: 20px;">
                        <h2 class="section-title"><i class="fas fa-wallet"></i> วิธีการชำระเงิน</h2>
                        <div class="payment-methods">
                            <label class="payment-method" onclick="selectPayment(this)">
                                <input type="radio" name="payment_method" value="credit_card" required>
                                <div class="payment-icon"><i class="fas fa-credit-card"></i></div>
                                <div>
                                    <strong>บัตรเครดิต/เดบิต</strong>
                                    <p style="margin: 0; font-size: 13px; color: #7f8c8d;">Visa, Mastercard, JCB</p>
                                </div>
                            </label>

                            <label class="payment-method" onclick="selectPayment(this)">
                                <input type="radio" name="payment_method" value="promptpay" required>
                                <div class="payment-icon"><i class="fas fa-qrcode"></i></div>
                                <div>
                                    <strong>พร้อมเพย์</strong>
                                    <p style="margin: 0; font-size: 13px; color: #7f8c8d;">สแกน QR Code</p>
                                </div>
                            </label>

                            <label class="payment-method" onclick="selectPayment(this)">
                                <input type="radio" name="payment_method" value="bank_transfer" required>
                                <div class="payment-icon"><i class="fas fa-university"></i></div>
                                <div>
                                    <strong>โอนเงินผ่านธนาคาร</strong>
                                    <p style="margin: 0; font-size: 13px; color: #7f8c8d;">โอนผ่าน Mobile Banking</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Order Summary -->
                <div>
                    <div class="checkout-section order-summary">
                        <h2 class="section-title"><i class="fas fa-shopping-cart"></i> สรุปคำสั่งซื้อ</h2>

                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="course-item">
                                    <img src="<?php echo htmlspecialchars($item['thumbnail']); ?>" alt="<?php echo htmlspecialchars($item['course_title']); ?>" class="course-thumb">
                                    <div class="course-info">
                                        <h4><?php echo htmlspecialchars($item['course_title']); ?></h4>
                                        <div class="course-price">
                                            <?php if ($item['discount_price']): ?>
                                                <s style="color: #95a5a6;">฿<?php echo number_format($item['price']); ?></s>
                                                ฿<?php echo number_format($item['discount_price']); ?>
                                            <?php else: ?>
                                                ฿<?php echo number_format($item['price']); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="summary-item">
                            <span>จำนวนคอร์ส</span>
                            <span><?php echo count($cart_items); ?> คอร์ส</span>
                        </div>

                        <div class="summary-total">
                            <span>ยอดรวมทั้งหมด</span>
                            <span>฿<?php echo number_format($total_price); ?></span>
                        </div>

                        <button type="submit" name="process_payment" class="btn-checkout">
                            <i class="fas fa-lock"></i> ดำเนินการชำระเงิน
                        </button>

                        <p style="text-align: center; margin-top: 15px; font-size: 13px; color: #7f8c8d;">
                            <i class="fas fa-shield-alt"></i> การชำระเงินปลอดภัย 100%
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function selectPayment(element) {
            // ลบ class selected จากทุก payment method
            document.querySelectorAll('.payment-method').forEach(method => {
                method.classList.remove('selected');
            });

            // เพิ่ม class selected ให้ที่เลือก
            element.classList.add('selected');

            // เช็ค radio button
            element.querySelector('input[type="radio"]').checked = true;
        }

        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');

            if (!paymentMethod) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณาเลือกวิธีการชำระเงิน',
                    confirmButtonColor: '#667eea',
                    confirmButtonText: 'ตกลง'
                });
            }
        });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>

</html>