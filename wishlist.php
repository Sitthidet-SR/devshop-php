<?php
session_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/cart_functions.php';
require_once 'includes/course_functions.php';
require_once 'includes/auth_functions.php';

// ตรวจสอบว่า login หรือไม่
if (!is_logged_in()) {
    header('Location: login.php?redirect=wishlist.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

// ลบออกจากรายการถูกใจ
if (isset($_GET['remove'])) {
    $course_id = intval($_GET['remove']);
    $sql = "DELETE FROM wishlist WHERE user_id = $user_id AND course_id = $course_id";
    if ($conn->query($sql)) {
        require_once 'includes/redirect_helper.php';
        redirect_self_with_message('removed_from_wishlist');
    }
}

// เพิ่มลงตะกร้า
if (isset($_GET['add_to_cart'])) {
    $course_id = intval($_GET['add_to_cart']);
    
    // ตรวจสอบว่ามีในตะกร้าแล้วหรือไม่
    $check_sql = "SELECT * FROM cart WHERE user_id = $user_id AND course_id = $course_id";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows == 0) {
        $sql = "INSERT INTO cart (user_id, course_id) VALUES ($user_id, $course_id)";
        if ($conn->query($sql)) {
            require_once 'includes/redirect_helper.php';
            redirect_self_with_message('added_to_cart');
        }
    } else {
        require_once 'includes/redirect_helper.php';
        redirect_self_with_message('already_in_cart');
    }
}

// ดึงรายการถูกใจ
$wishlist_items = get_wishlist_items($user_id);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการถูกใจ - DevShop</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/sweetalert-helper.js?v=<?php echo time(); ?>"></script>
    <link rel="stylesheet" href="assets/css/wishlist.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="wishlist-container">
        <div class="page-header">
            <h1><i class="fas fa-heart"></i> รายการถูกใจ</h1>
            <p style="color: #7f8c8d;">คอร์สที่คุณสนใจ</p>
        </div>



        <?php if (empty($wishlist_items)): ?>
            <div class="empty-wishlist">
                <i class="fas fa-heart"></i>
                <h2>ยังไม่มีรายการถูกใจ</h2>
                <p>เพิ่มคอร์สที่คุณสนใจลงในรายการถูกใจ</p>
                <a href="courses.php" class="btn-browse">เลือกดูคอร์ส</a>
            </div>
        <?php else: ?>
            <div class="wishlist-grid">
                <?php foreach ($wishlist_items as $item): ?>
                    <div class="wishlist-card">
                        <div class="card-image">
                            <img src="<?php echo htmlspecialchars($item['thumbnail']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['course_title']); ?>">
                        </div>
                        <div class="card-content">
                            <h3 class="card-title"><?php echo htmlspecialchars($item['course_title']); ?></h3>
                            <p class="card-instructor">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($item['instructor_name']); ?>
                            </p>
                            <div class="card-price">
                                <?php if ($item['discount_price']): ?>
                                    <span class="current-price">฿<?php echo format_price($item['discount_price']); ?></span>
                                    <span class="original-price">฿<?php echo format_price($item['price']); ?></span>
                                <?php else: ?>
                                    <span class="current-price">฿<?php echo format_price($item['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="card-actions">
                                <a href="?add_to_cart=<?php echo $item['course_id']; ?>" class="btn-add-cart">
                                    <i class="fas fa-shopping-cart"></i> เพิ่มลงตะกร้า
                                </a>
                                <a href="javascript:void(0)" 
                                   class="btn-remove-wish"
                                   onclick="confirmDelete('คุณต้องการลบคอร์สนี้ออกจากรายการถูกใจหรือไม่?', '?remove=<?php echo $item['course_id']; ?>')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>
