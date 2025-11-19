<?php
session_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth_functions.php';

// ตรวจสอบว่า login หรือไม่
if (!is_logged_in()) {
    header('Location: login.php?redirect=my_courses.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// ดึงคอร์สที่ซื้อแล้ว
$sql = "SELECT e.*, c.course_title, c.thumbnail, c.description,
        CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
        (SELECT COUNT(*) FROM lectures l 
         JOIN sections s ON l.section_id = s.section_id 
         WHERE s.course_id = c.course_id) as total_lessons
        FROM enrollments e
        JOIN courses c ON e.course_id = c.course_id
        LEFT JOIN users u ON c.instructor_id = u.user_id
        WHERE e.user_id = $user_id
        ORDER BY e.enrolled_at DESC";

$my_courses = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คอร์สของฉัน - DevShop</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/my-courses.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/sweetalert-helper.js?v=<?php echo time(); ?>"></script>
</head>

<body>
    <?php include 'includes/header.php'; ?>



    <div class="my-courses-container">
        <div class="page-header">
            <h1><i class="fas fa-graduation-cap"></i> คอร์สของฉัน</h1>
            <p>คอร์สทั้งหมดที่คุณซื้อและกำลังเรียนอยู่</p>
        </div>

        <?php if (!empty($my_courses)): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-value"><?php echo count($my_courses); ?></div>
                    <div class="stat-label">คอร์สทั้งหมด</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    <div class="stat-value">
                        <?php 
                        $in_progress = array_filter($my_courses, function($c) {
                            return $c['progress'] > 0 && $c['progress'] < 100;
                        });
                        echo count($in_progress);
                        ?>
                    </div>
                    <div class="stat-label">กำลังเรียน</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value">
                        <?php 
                        $completed = array_filter($my_courses, function($c) {
                            return $c['progress'] == 100;
                        });
                        echo count($completed);
                        ?>
                    </div>
                    <div class="stat-label">เรียนจบแล้ว</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value">
                        <?php 
                        $not_started = array_filter($my_courses, function($c) {
                            return $c['progress'] == 0;
                        });
                        echo count($not_started);
                        ?>
                    </div>
                    <div class="stat-label">ยังไม่เริ่มเรียน</div>
                </div>
            </div>

            <div class="courses-grid">
                <?php foreach ($my_courses as $course): ?>
                    <div class="course-card">
                        <div class="course-image-container">
                            <img src="<?php echo htmlspecialchars($course['thumbnail']); ?>" 
                                 alt="<?php echo htmlspecialchars($course['course_title']); ?>" 
                                 class="course-thumbnail">
                            <div class="course-progress-overlay">
                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px;">
                                    <span>ความคืบหน้า</span>
                                    <span><?php echo $course['progress']; ?>%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $course['progress']; ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="course-body">
                            <h3 class="course-title"><?php echo htmlspecialchars($course['course_title']); ?></h3>
                            <div class="course-instructor">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($course['instructor_name']); ?>
                            </div>
                            
                            <?php if ($course['progress'] == 100): ?>
                                <div style="margin-bottom: 15px;">
                                    <span class="completed-badge">
                                        <i class="fas fa-trophy"></i> เรียนจบแล้ว
                                    </span>
                                </div>
                            <?php endif; ?>

                            <div class="course-meta">
                                <div class="course-lessons">
                                    <i class="fas fa-play-circle"></i> 
                                    <?php echo $course['total_lessons']; ?> บทเรียน
                                </div>
                                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                    <a href="course_learn.php?id=<?php echo $course['course_id']; ?>" class="btn-continue">
                                        <?php echo $course['progress'] == 0 ? 'เริ่มเรียน' : ($course['progress'] == 100 ? 'ทบทวน' : 'เรียนต่อ'); ?>
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                    <a href="course_review.php?course_id=<?php echo $course['course_id']; ?>" class="btn-review">
                                        <i class="fas fa-star"></i> รีวิว
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-book-open"></i>
                <h2>คุณยังไม่มีคอร์ส</h2>
                <p>เริ่มต้นการเรียนรู้ของคุณด้วยการเลือกคอร์สที่คุณสนใจ</p>
                <a href="courses.php" class="btn-browse">
                    <i class="fas fa-search"></i> เลือกดูคอร์ส
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>
