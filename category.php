<?php
session_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/category_functions.php';
require_once 'includes/course_functions.php';
require_once 'includes/cart_functions.php';

// รับ slug จาก URL
$category_slug = $_GET['slug'] ?? '';

if (empty($category_slug)) {
    header('Location: courses.php');
    exit;
}

// ดึงข้อมูลหมวดหมู่
$category = get_category_by_slug($category_slug);

if (!$category) {
    header('Location: courses.php');
    exit;
}

// ดึงคอร์สในหมวดหมู่นี้
$courses = get_courses_by_category($category_slug);

// ดึงหมวดหมู่ทั้งหมดสำหรับ filter
$all_categories = get_all_categories();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['category_name']); ?> - DevShop</title>
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
            <h1>
                <i class="fas <?php echo htmlspecialchars($category['icon']); ?>"></i> 
                <?php echo htmlspecialchars($category['category_name']); ?>
            </h1>
            <?php if ($category['description']): ?>
                <p><?php echo htmlspecialchars($category['description']); ?></p>
            <?php endif; ?>
        </div>

        <div class="filter-section">
            <div class="filter-title"><i class="fas fa-filter"></i> กรองตามหมวดหมู่</div>
            <div class="category-filters">
                <a href="courses.php" class="filter-btn">ทั้งหมด</a>
                <?php foreach ($all_categories as $cat): ?>
                    <a href="category.php?slug=<?php echo htmlspecialchars($cat['category_slug']); ?>" 
                       class="filter-btn <?php echo $cat['category_slug'] === $category_slug ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="courses-count">
            <strong><?php echo count($courses); ?></strong> คอร์สในหมวดหมู่นี้
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
                    <p>ยังไม่มีคอร์สในหมวดหมู่นี้</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>
