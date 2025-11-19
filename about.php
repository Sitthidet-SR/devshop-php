<?php
session_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/cart_functions.php';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เกี่ยวกับเรา - DevShop</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/about.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="about-hero">
        <h1>เกี่ยวกับ DevShop</h1>
        <p>แพลตฟอร์มเรียนรู้การเขียนโปรแกรมออนไลน์ที่ดีที่สุด พัฒนาทักษะของคุณไปกับเรา</p>
    </div>

    <div class="about-content">
        <div class="about-section mission-section">
            <div class="container">
                <div class="mission-content">
                    <div class="mission-text">
                        <h2>พันธกิจของเรา</h2>
                        <p>
                            DevShop มุ่งมั่นที่จะทำให้การเรียนรู้การเขียนโปรแกรมเข้าถึงได้ง่ายสำหรับทุกคน 
                            เราเชื่อว่าทุกคนสามารถเรียนรู้การเขียนโค้ดได้ ไม่ว่าจะเริ่มต้นจากจุดไหน 
                            ด้วยคอร์สคุณภาพจากผู้เชี่ยวชาญและการสนับสนุนจากชุมชนที่แข็งแกร่ง
                        </p>
                        <div class="mission-highlights">
                            <div class="highlight-item">
                                <i class="fas fa-check-circle"></i>
                                <span>คอร์สคุณภาพจากผู้เชี่ยวชาญ</span>
                            </div>
                            <div class="highlight-item">
                                <i class="fas fa-check-circle"></i>
                                <span>เรียนได้ทุกที่ทุกเวลา</span>
                            </div>
                            <div class="highlight-item">
                                <i class="fas fa-check-circle"></i>
                                <span>ชุมชนที่แข็งแกร่งและพร้อมช่วยเหลือ</span>
                            </div>
                        </div>
                    </div>
                    <div class="mission-image">
                        <div class="image-decoration">
                            <i class="fas fa-code"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="about-section features-section">
            <div class="container">
                <h2>ทำไมต้องเลือก DevShop</h2>
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
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <h3>เนื้อหาทันสมัย</h3>
                        <p>อัพเดทเนื้อหาให้ทันกับเทคโนโลยีใหม่ๆ อยู่เสมอ</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>ชุมชนนักเรียน</h3>
                        <p>เข้าร่วมชุมชนนักเรียนและแลกเปลี่ยนประสบการณ์</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>
