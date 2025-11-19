<?php
session_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/category_functions.php';
require_once 'includes/course_functions.php';
require_once 'includes/cart_functions.php';

// ดึงข้อมูลคอร์สทั้งหมด
$courses = get_all_courses();

// ดึงหมวดหมู่สำหรับ filter
$categories = get_all_categories();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คอร์สทั้งหมด - DevShop</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/sweetalert-helper.js?v=<?php echo time(); ?>"></script>
    <link rel="stylesheet" href="assets/css/courses.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>



    <div class="courses-page">
        <div class="page-header">
            <h1><i class="fas fa-book"></i> คอร์สทั้งหมด</h1>
            <p>เลือกเรียนคอร์สที่คุณสนใจ</p>
        </div>

        <div class="filter-section">
            <div class="filter-title"><i class="fas fa-filter"></i> กรองตามหมวดหมู่</div>
            <div class="category-filters">
                <a href="courses.php" class="filter-btn active">ทั้งหมด</a>
                <?php foreach ($categories as $category): ?>
                    <a href="category.php?slug=<?php echo htmlspecialchars($category['category_slug']); ?>" 
                       class="filter-btn">
                        <?php echo htmlspecialchars($category['category_name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="courses-count">
            <strong><?php echo count($courses); ?></strong> คอร์สทั้งหมด
        </div>

        <div class="courses-grid">
            <?php if (!empty($courses)): ?>
                <?php foreach ($courses as $course): ?>
                    <?php include 'includes/course_card.php'; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-book-open"></i>
                    <h3>ไม่พบคอร์ส</h3>
                    <p>ขออภัย ไม่พบคอร์สในหมวดหมู่นี้</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>
