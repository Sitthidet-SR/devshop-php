<?php
// เริ่ม session ก่อนทุกอย่าง
session_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/category_functions.php';
require_once 'includes/course_functions.php';
require_once 'includes/cart_functions.php';

// ดึงข้อมูลหมวดหมู่
$categories = get_all_categories();

// ดึงข้อมูลคอร์สแนะนำ
$featured_courses = get_featured_courses(4);

// ดึงข้อมูลคอร์สขายดี
$bestseller_courses = get_bestseller_courses(4);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevShop - เรียนรู้การเขียนโปรแกรมออนไลน์</title>
    <link rel="icon" type="image/png" href="/devshop/favicon.png">
    <link rel="shortcut icon" type="image/png" href="/devshop/favicon.png">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/sweetalert-helper.js?v=<?php echo time(); ?>"></script>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title">เรียนรู้การเขียนโปรแกรม<br><span class="highlight">กับคอร์สคุณภาพ</span></h1>
                <p class="hero-description">พัฒนาทักษะการเขียนโค้ดของคุณกับคอร์สออนไลน์จากผู้เชี่ยวชาญ<br>เรียนได้ทุกที่ทุกเวลา ตามจังหวะของคุณ</p>
                <div class="hero-buttons">
                    <a href="courses.php" class="btn-primary"><i class="fas fa-play-circle"></i> เริ่มเรียนเลย</a>
                    <a href="about.php" class="btn-secondary"><i class="fas fa-info-circle"></i> เรียนรู้เพิ่มเติม</a>
                </div>
            </div>
            <div class="hero-image">
                <div class="code-window">
                    <div class="window-header">
                        <span class="dot red"></span>
                        <span class="dot yellow"></span>
                        <span class="dot green"></span>
                    </div>
                    <div class="code-content">
                        <pre><code><span class="code-keyword">function</span> <span class="code-function">learnCoding</span>() {
  <span class="code-keyword">const</span> skills = [
    <span class="code-string">'HTML'</span>,
    <span class="code-string">'CSS'</span>,
    <span class="code-string">'JavaScript'</span>,
    <span class="code-string">'PHP'</span>,
    <span class="code-string">'Python'</span>
  ];
  
  <span class="code-keyword">return</span> skills.<span class="code-function">map</span>(skill => 
    <span class="code-string">`เรียนรู้ ${skill}`</span>
  );
}</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories">
        <div class="container">
            <h2 class="section-title">หมวดหมู่ยอดนิยม</h2>
            <p class="section-subtitle">เลือกเรียนในสิ่งที่คุณสนใจ</p>
            <div class="categories-grid">
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                        <a href="category.php?slug=<?php echo htmlspecialchars($category['category_slug']); ?>" class="category-card">
                            <i class="fas <?php echo htmlspecialchars($category['icon']); ?>"></i>
                            <h3><?php echo htmlspecialchars($category['category_name']); ?></h3>
                            <p><?php echo $category['course_count']; ?> คอร์ส</p>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>ไม่พบหมวดหมู่</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Featured Courses Section -->
    <section class="featured-courses">
        <div class="container">
            <h2 class="section-title">คอร์สแนะนำ</h2>
            <p class="section-subtitle">คอร์สยอดนิยมที่คนเลือกเรียนมากที่สุด</p>


            <div class="courses-grid">
                <?php if (!empty($featured_courses)): ?>
                    <?php foreach ($featured_courses as $course): ?>
                        <?php include 'includes/course_card.php'; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-book-open"></i>
                        <h3>ยังไม่มีคอร์สแนะนำ</h3>
                        <p>กำลังเตรียมคอร์สคุณภาพมาให้เร็วๆ นี้</p>
                        <a href="courses.php" class="btn-primary">ดูคอร์สทั้งหมด</a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="text-center">
                <a href="courses.php" class="btn-view-all">ดูคอร์สทั้งหมด <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <!-- Bestseller Courses Section -->
    <section class="bestseller-courses">
        <div class="container">
            <h2 class="section-title">🔥 คอร์สขายดี</h2>
            <p class="section-subtitle">คอร์สที่ผู้เรียนเลือกมากที่สุด</p>

            <div class="courses-grid">
                <?php if (!empty($bestseller_courses)): ?>
                    <?php foreach ($bestseller_courses as $course): ?>
                        <?php include 'includes/course_card.php'; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-fire"></i>
                        <h3>ยังไม่มีคอร์สขายดี</h3>
                        <p>กำลังรวบรวมคอร์สยอดนิยมมาให้เร็วๆ นี้</p>
                        <a href="courses.php" class="btn-primary">ดูคอร์สทั้งหมด</a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="text-center">
                <a href="courses.php" class="btn-view-all">ดูคอร์สทั้งหมด <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose">
        <div class="container">
            <h2 class="section-title">ทำไมต้องเลือก DevShop</h2>
            <p class="section-subtitle">เหตุผลที่คุณควรเรียนกับเรา</p>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h3>ผู้สอนมืออาชีพ</h3>
                    <p>เรียนจากผู้เชี่ยวชาญที่มีประสบการณ์จริงในสายงาน</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-infinity"></i>
                    </div>
                    <h3>เรียนได้ไม่จำกัด</h3>
                    <p>เข้าถึงคอร์สได้ตลอดชีพ เรียนซ้ำได้ไม่จำกัดครั้ง</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h3>ใบประกาศนียบัตร</h3>
                    <p>รับใบประกาศนียบัตรเมื่อจบคอร์ส เพิ่มมูลค่าให้ Resume</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>ซัพพอร์ตตลอด 24/7</h3>
                    <p>ทีมงานพร้อมช่วยเหลือคุณทุกเวลาที่ต้องการ</p>
                </div>
            </div>
        </div>
    </section>
    <?php include 'includes/footer.php'; ?>
</body>

</html>