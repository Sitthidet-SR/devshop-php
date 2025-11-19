<?php
// ตรวจสอบว่ามีการเชื่อมต่อฐานข้อมูลหรือยัง
if (!isset($conn)) {
    require_once __DIR__ . '/config.php';
}

// โหลดฟังก์ชันที่จำเป็น
if (!function_exists('get_cart_count')) {
    require_once __DIR__ . '/cart_functions.php';
}

if (!function_exists('get_all_categories')) {
    require_once __DIR__ . '/category_functions.php';
}

// ตรวจสอบว่า user login หรือไม่
$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;

// ดึงชื่อผู้ใช้จากฐานข้อมูลเพื่อให้ได้ข้อมูลล่าสุด
if ($is_logged_in) {
    $user_result = $conn->query("SELECT first_name, last_name FROM users WHERE user_id = $user_id");
    if ($user_result && $user_result->num_rows > 0) {
        $user_data = $user_result->fetch_assoc();
        $user_name = $user_data['first_name'] . ' ' . $user_data['last_name'];
        // อัพเดท session
        $_SESSION['user_name'] = $user_name;
    } else {
        $user_name = $_SESSION['user_name'] ?? '';
    }
} else {
    $user_name = '';
}

// นับจำนวนสินค้าในตะกร้าและรายการถูกใจ
$cart_count = get_cart_count($user_id);
$wishlist_count = get_wishlist_count($user_id);

// ดึงหมวดหมู่สำหรับเมนู (ถ้ามี)
if (!isset($categories)) {
    require_once __DIR__ . '/category_functions.php';
    $header_categories = get_all_categories();
} else {
    $header_categories = $categories;
}
?>
<header class="header">
    <div class="header-container">
        <div class="header-top">
            <a href="index.php" class="logo">
                <i class="fas fa-code"></i>
                <span class="logo-text">DevShop</span>
            </a>

            <div class="search-bar">
                <form class="search-form" action="search.php" method="GET">
                    <input type="text" class="search-input" name="q" placeholder="ค้นหาคอร์สที่คุณสนใจ..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>

            <div class="header-actions">
                <a href="wishlist.php" class="action-btn" title="รายการถูกใจ">
                    <i class="fas fa-heart"></i>
                    <span>ถูกใจ</span>
                    <?php if ($wishlist_count > 0): ?>
                        <span class="badge"><?php echo $wishlist_count; ?></span>
                    <?php endif; ?>
                </a>

                <a href="cart.php" class="action-btn" title="ตะกร้าสินค้า">
                    <i class="fas fa-shopping-cart"></i>
                    <span>ตะกร้า</span>
                    <?php if ($cart_count > 0): ?>
                        <span class="badge"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>

                <div class="auth-buttons">
                    <?php if ($is_logged_in): ?>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <a href="admin/index.php" class="btn-admin" title="หลังบ้าน">
                                <i class="fas fa-cog"></i> Admin
                            </a>
                        <?php endif; ?>
                        <a href="profile.php" class="btn-login">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($user_name); ?>
                        </a>
                        <a href="logout.php" class="btn-register">ออกจากระบบ</a>
                    <?php else: ?>
                        <a href="login.php" class="btn-login">เข้าสู่ระบบ</a>
                        <a href="register.php" class="btn-register">สมัครสมาชิก</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <nav class="nav-menu">
            <ul class="nav-list">
                <li class="nav-item"><a href="index.php"><i class="fas fa-home"></i> หน้าแรก</a></li>
                <li class="nav-item"><a href="courses.php"><i class="fas fa-book"></i> คอร์สทั้งหมด</a></li>
                <?php if ($is_logged_in): ?>
                    <li class="nav-item"><a href="my_courses.php"><i class="fas fa-graduation-cap"></i> คอร์สของฉัน</a></li>
                <?php endif; ?>
                <li class="nav-item"><a href="about.php"><i class="fas fa-info-circle"></i> เกี่ยวกับเรา</a></li>
                <li class="nav-item"><a href="contact.php"><i class="fas fa-envelope"></i> ติดต่อเรา</a></li>
            </ul>
        </nav>
    </div>
</header>