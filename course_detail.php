<?php
session_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/course_functions.php';
require_once 'includes/cart_functions.php';

// ตรวจสอบ course ID
if (!isset($_GET['id'])) {
    header('Location: courses.php');
    exit;
}

$course_id = intval($_GET['id']);
$course = get_course_by_id($course_id);

if (!$course) {
    header('Location: courses.php');
    exit;
}

// ดึงรีวิว
$reviews = $conn->query("
    SELECT r.*, CONCAT(u.first_name, ' ', u.last_name) as user_name
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    WHERE r.course_id = $course_id AND r.status = 'approved'
    ORDER BY r.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// ดึงเนื้อหาคอร์ส
$sections = $conn->query("
    SELECT s.*, 
    (SELECT COUNT(*) FROM lectures WHERE section_id = s.section_id) as lecture_count
    FROM sections s
    WHERE s.course_id = $course_id
    ORDER BY s.section_order
")->fetch_all(MYSQLI_ASSOC);

foreach ($sections as &$section) {
    $section['lectures'] = $conn->query("
        SELECT * FROM lectures 
        WHERE section_id = {$section['section_id']}
        ORDER BY lecture_order
    ")->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['course_title']); ?> - DevShop</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="course-detail-container">
        <!-- Course Header -->
        <div class="course-header">
            <div class="container">
                <a href="courses.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> กลับไปหน้าคอร์ส
                </a>
                <div class="course-header-content">
                    <div class="course-header-left">
                        <h1><?php echo htmlspecialchars($course['course_title']); ?></h1>
                        <p class="course-short-desc"><?php echo htmlspecialchars($course['short_description']); ?></p>
                        <div class="course-meta-info">
                            <span class="rating">
                                <i class="fas fa-star"></i> <?php echo number_format($course['avg_rating'], 1); ?>
                                (<?php echo $course['review_count']; ?> รีวิว)
                            </span>
                            <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($course['instructor_name']); ?></span>
                            <span><i class="fas fa-clock"></i> <?php echo $course['duration_hours']; ?> ชั่วโมง</span>
                            <span><i class="fas fa-signal"></i> <?php echo get_level_text($course['level']); ?></span>
                        </div>
                    </div>
                    <div class="course-header-right">
                        <img src="<?php echo htmlspecialchars($course['thumbnail']); ?>" 
                             alt="<?php echo htmlspecialchars($course['course_title']); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Content -->
        <div class="container">
            <div class="course-detail-grid">
                <!-- Main Content -->
                <div class="course-main">
                    <!-- Description -->
                    <div class="content-section">
                        <h2><i class="fas fa-info-circle"></i> รายละเอียดคอร์ส</h2>
                        <div class="course-description">
                            <?php echo nl2br(htmlspecialchars($course['description'])); ?>
                        </div>
                    </div>

                    <!-- Curriculum -->
                    <div class="content-section">
                        <h2><i class="fas fa-list"></i> เนื้อหาในคอร์ส</h2>
                        <div class="curriculum">
                            <?php foreach ($sections as $section): ?>
                                <div class="section-item">
                                    <div class="section-header">
                                        <h3><?php echo htmlspecialchars($section['section_title']); ?></h3>
                                        <span><?php echo $section['lecture_count']; ?> บทเรียน</span>
                                    </div>
                                    <div class="lectures-list">
                                        <?php foreach ($section['lectures'] as $lecture): ?>
                                            <div class="lecture-item">
                                                <i class="fas fa-play-circle"></i>
                                                <span><?php echo htmlspecialchars($lecture['lecture_title']); ?></span>
                                                <?php if ($lecture['duration_minutes']): ?>
                                                    <span class="duration"><?php echo $lecture['duration_minutes']; ?> นาที</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Reviews -->
                    <?php if (!empty($reviews)): ?>
                        <div class="content-section">
                            <h2><i class="fas fa-star"></i> รีวิวจากผู้เรียน</h2>
                            <div class="reviews-list">
                                <?php foreach ($reviews as $review): ?>
                                    <div class="review-item">
                                        <div class="review-header">
                                            <strong><?php echo htmlspecialchars($review['user_name']); ?></strong>
                                            <div class="rating">
                                                <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                                    <i class="fas fa-star"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <p><?php echo htmlspecialchars($review['comment']); ?></p>
                                        <small><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="course-sidebar">
                    <div class="price-card">
                        <div class="price">
                            <?php if ($course['discount_price'] && $course['discount_price'] < $course['price']): ?>
                                <span class="old-price">฿<?php echo number_format($course['price']); ?></span>
                                <span class="new-price">฿<?php echo number_format($course['discount_price']); ?></span>
                            <?php else: ?>
                                <span class="new-price">฿<?php echo number_format($course['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="add_to_cart.php?course_id=<?php echo $course_id; ?>" class="btn-primary btn-block">
                            <i class="fas fa-shopping-cart"></i> เพิ่มลงตะกร้า
                        </a>
                        <a href="add_to_wishlist.php?course_id=<?php echo $course_id; ?>" class="btn-secondary btn-block">
                            <i class="fas fa-heart"></i> เพิ่มลงรายการถูกใจ
                        </a>
                    </div>

                    <div class="info-card">
                        <h3>ข้อมูลคอร์ส</h3>
                        <ul>
                            <li><i class="fas fa-clock"></i> ระยะเวลา: <?php echo $course['duration_hours']; ?> ชั่วโมง</li>
                            <li><i class="fas fa-book"></i> บทเรียน: <?php echo $course['total_lectures']; ?> บท</li>
                            <li><i class="fas fa-signal"></i> ระดับ: <?php echo get_level_text($course['level']); ?></li>
                            <li><i class="fas fa-infinity"></i> เข้าถึงตลอดชีพ</li>
                            <li><i class="fas fa-certificate"></i> ใบประกาศนียบัตร</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
