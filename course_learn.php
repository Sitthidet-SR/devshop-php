<?php
session_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth_functions.php';

// ตรวจสอบว่า login หรือไม่
if (!is_logged_in()) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$user_id = $_SESSION['user_id'];

// ตรวจสอบ course_id
if (!isset($_GET['id'])) {
    header('Location: my_courses.php');
    exit;
}

$course_id = intval($_GET['id']);

// ตรวจสอบว่าผู้ใช้ซื้อคอร์สนี้แล้วหรือไม่
$enrollment_check = $conn->query("SELECT * FROM enrollments WHERE user_id = $user_id AND course_id = $course_id");
if ($enrollment_check->num_rows == 0) {
    header('Location: course_detail.php?id=' . $course_id);
    exit;
}

$enrollment = $enrollment_check->fetch_assoc();

// ดึงข้อมูลคอร์ส
$course_sql = "SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) as instructor_name
               FROM courses c
               LEFT JOIN users u ON c.instructor_id = u.user_id
               WHERE c.course_id = $course_id";
$course = $conn->query($course_sql)->fetch_assoc();

if (!$course) {
    header('Location: my_courses.php');
    exit;
}

// ดึง sections และ lectures
$sections_sql = "SELECT * FROM sections WHERE course_id = $course_id ORDER BY section_order ASC";
$sections = $conn->query($sections_sql)->fetch_all(MYSQLI_ASSOC);

// ดึง lectures สำหรับแต่ละ section
foreach ($sections as &$section) {
    $section_id = $section['section_id'];
    $lectures_sql = "SELECT * FROM lectures WHERE section_id = $section_id ORDER BY lecture_order ASC";
    $section['lectures'] = $conn->query($lectures_sql)->fetch_all(MYSQLI_ASSOC);
}

// ดึง lecture ที่เลือก
$current_lecture_id = isset($_GET['lecture']) ? intval($_GET['lecture']) : null;
$current_lecture = null;

if ($current_lecture_id) {
    $lecture_sql = "SELECT l.*, s.section_title 
                    FROM lectures l
                    JOIN sections s ON l.section_id = s.section_id
                    WHERE l.lecture_id = $current_lecture_id";
    $current_lecture = $conn->query($lecture_sql)->fetch_assoc();
} else {
    // ถ้าไม่มีการเลือก lecture ให้เลือก lecture แรก
    if (!empty($sections) && !empty($sections[0]['lectures'])) {
        $current_lecture = $sections[0]['lectures'][0];
        $current_lecture_id = $current_lecture['lecture_id'];
    }
}

// นับจำนวน lectures ทั้งหมด
$total_lectures = 0;
foreach ($sections as $section) {
    $total_lectures += count($section['lectures']);
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['course_title']); ?> - เรียนคอร์ส</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/course-learn.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="learn-container">
        <!-- Main Content -->
        <div class="learn-main">
            <!-- Video Player -->
            <div class="video-container">
                <?php if ($current_lecture && $current_lecture['lecture_type'] == 'video' && $current_lecture['content_url']): ?>
                    <video class="video-player" controls controlsList="nodownload">
                        <source src="<?php echo htmlspecialchars($current_lecture['content_url']); ?>" type="video/mp4">
                        เบราว์เซอร์ของคุณไม่รองรับการเล่นวิดีโอ
                    </video>
                <?php else: ?>
                    <div class="video-placeholder">
                        <i class="fas fa-play-circle"></i>
                        <p><?php echo $current_lecture ? 'ไม่มีวิดีโอสำหรับบทเรียนนี้' : 'เลือกบทเรียนเพื่อเริ่มเรียน'; ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Content Area -->
            <div class="content-area">
                <?php if ($current_lecture): ?>
                    <div class="lecture-header">
                        <h1 class="lecture-title"><?php echo htmlspecialchars($current_lecture['lecture_title']); ?></h1>
                        <div class="lecture-meta">
                            <span><i class="fas fa-folder"></i> <?php echo htmlspecialchars($current_lecture['section_title']); ?></span>
                            <?php if ($current_lecture['duration_minutes']): ?>
                                <span><i class="fas fa-clock"></i> <?php echo $current_lecture['duration_minutes']; ?> นาที</span>
                            <?php endif; ?>
                            <span><i class="fas fa-<?php echo $current_lecture['lecture_type'] == 'video' ? 'video' : 'file-alt'; ?>"></i> 
                                <?php 
                                $types = ['video' => 'วิดีโอ', 'article' => 'บทความ', 'quiz' => 'แบบทดสอบ', 'file' => 'ไฟล์'];
                                echo $types[$current_lecture['lecture_type']];
                                ?>
                            </span>
                        </div>
                    </div>

                    <?php if ($current_lecture['content_text']): ?>
                        <div class="lecture-content">
                            <?php echo nl2br(htmlspecialchars($current_lecture['content_text'])); ?>
                        </div>
                    <?php endif; ?>

                    <div class="lecture-navigation">
                        <a href="#" class="nav-btn nav-btn-prev">
                            <i class="fas fa-arrow-left"></i> บทก่อนหน้า
                        </a>
                        <a href="#" class="nav-btn nav-btn-next">
                            บทถัดไป <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 60px 20px; color: #7f8c8d;">
                        <i class="fas fa-book-open" style="font-size: 80px; margin-bottom: 20px; opacity: 0.3;"></i>
                        <h2>ยังไม่มีบทเรียน</h2>
                        <p>คอร์สนี้ยังไม่มีเนื้อหาบทเรียน</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="learn-sidebar" id="sidebar">
            <div class="back-to-courses">
                <a href="my_courses.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> กลับไปคอร์สของฉัน
                </a>
            </div>

            <div class="sidebar-header">
                <h2 style="font-size: 18px; color: #2c3e50; margin-bottom: 5px;">
                    <?php echo htmlspecialchars($course['course_title']); ?>
                </h2>
                <p style="font-size: 13px; color: #7f8c8d;">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($course['instructor_name']); ?>
                </p>

                <div class="course-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $enrollment['progress']; ?>%"></div>
                    </div>
                    <div class="progress-text">
                        <span>ความคืบหน้า</span>
                        <span><strong><?php echo $enrollment['progress']; ?>%</strong></span>
                    </div>
                </div>
            </div>

            <div class="curriculum">
                <h3 style="font-size: 16px; color: #2c3e50; margin-bottom: 15px;">
                    <i class="fas fa-list"></i> เนื้อหาคอร์ส
                </h3>

                <?php if (empty($sections)): ?>
                    <p style="text-align: center; color: #7f8c8d; padding: 40px 20px;">
                        <i class="fas fa-inbox" style="font-size: 48px; display: block; margin-bottom: 15px; opacity: 0.3;"></i>
                        ยังไม่มีเนื้อหาบทเรียน
                    </p>
                <?php else: ?>
                    <?php foreach ($sections as $section): ?>
                        <div class="section">
                            <div class="section-header">
                                <span><?php echo htmlspecialchars($section['section_title']); ?></span>
                                <span style="font-size: 12px; color: #7f8c8d;">
                                    <?php echo count($section['lectures']); ?> บทเรียน
                                </span>
                            </div>
                            <div class="section-lectures">
                                <?php foreach ($section['lectures'] as $lecture): ?>
                                    <a href="?id=<?php echo $course_id; ?>&lecture=<?php echo $lecture['lecture_id']; ?>" 
                                       class="lecture-item <?php echo $current_lecture_id == $lecture['lecture_id'] ? 'active' : ''; ?>">
                                        <div class="lecture-icon">
                                            <i class="fas fa-<?php echo $lecture['lecture_type'] == 'video' ? 'play' : 'file-alt'; ?>"></i>
                                        </div>
                                        <div class="lecture-info">
                                            <div class="lecture-name"><?php echo htmlspecialchars($lecture['lecture_title']); ?></div>
                                            <?php if ($lecture['duration_minutes']): ?>
                                                <div class="lecture-duration">
                                                    <i class="fas fa-clock"></i> <?php echo $lecture['duration_minutes']; ?> นาที
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar Toggle Button (Mobile) -->
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-list"></i>
    </button>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
        }

        // ปิด sidebar เมื่อคลิกนอก sidebar (mobile)
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.sidebar-toggle');
            
            if (window.innerWidth <= 1024) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });

        // อัพเดท progress เมื่อดูบทเรียน
        function updateProgress(lectureId) {
            const courseId = <?php echo $course_id; ?>;
            
            fetch('update_progress.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `course_id=${courseId}&lecture_id=${lectureId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // อัพเดท progress bar
                    const progressFill = document.querySelector('.progress-fill');
                    const progressText = document.querySelector('.progress-text strong');
                    
                    if (progressFill) {
                        progressFill.style.width = data.progress + '%';
                    }
                    if (progressText) {
                        progressText.textContent = data.progress + '%';
                    }
                    
                    // เปลี่ยนสีของบทเรียนที่เรียนแล้ว
                    const lectureItem = document.querySelector(`a[href*="lecture_id=${lectureId}"]`);
                    if (lectureItem && !lectureItem.classList.contains('completed')) {
                        lectureItem.classList.add('completed');
                        lectureItem.innerHTML = '<i class="fas fa-check-circle"></i> ' + lectureItem.textContent.trim();
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // เรียก updateProgress เมื่อโหลดหน้า (ถ้ามี lecture_id)
        <?php if ($current_lecture_id): ?>
        updateProgress(<?php echo $current_lecture_id; ?>);
        <?php endif; ?>
    </script>
</body>

</html>
