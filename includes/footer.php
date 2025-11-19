<footer class="footer">
    <div class="footer-container">
        <div class="footer-content">
            <div class="footer-section">
                <h3 class="footer-logo">
                    <i class="fas fa-code"></i>
                    DevShop
                </h3>
                <p class="footer-description">แพลตฟอร์มเรียนรู้การเขียนโปรแกรมออนไลน์ที่ดีที่สุด พัฒนาทักษะของคุณไปกับเรา</p>
                <div class="social-links">
                    <a href="https://www.facebook.com/SitthidetSR/" target="_blank" class="social-link" title="Facebook">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a href="https://www.instagram.com/sitthidet_jack/" target="_blank" class="social-link" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://www.youtube.com/@sitthidet-sr" target="_blank" class="social-link" title="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="https://github.com/Sitthidet-SR" target="_blank" class="social-link" title="GitHub">
                        <i class="fab fa-github"></i>
                    </a>
                    <a href="https://line.me/ti/p/jack280645" target="_blank" class="social-link" title="LINE">
                        <i class="fab fa-line"></i>
                    </a>
                </div>
            </div>

            <div class="footer-section">
                <h4 class="footer-title">เมนูหลัก</h4>
                <ul class="footer-links">
                    <li><a href="index.php"><i class="fas fa-angle-right"></i> หน้าแรก</a></li>
                    <li><a href="courses.php"><i class="fas fa-angle-right"></i> คอร์สทั้งหมด</a></li>
                    <li><a href="about.php"><i class="fas fa-angle-right"></i> เกี่ยวกับเรา</a></li>
                    <li><a href="contact.php"><i class="fas fa-angle-right"></i> ติดต่อเรา</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4 class="footer-title">บัญชีของฉัน</h4>
                <ul class="footer-links">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="profile.php"><i class="fas fa-angle-right"></i> โปรไฟล์</a></li>
                        <li><a href="my_courses.php"><i class="fas fa-angle-right"></i> คอร์สของฉัน</a></li>
                        <li><a href="order_history.php"><i class="fas fa-angle-right"></i> ประวัติการสั่งซื้อ</a></li>
                        <li><a href="wishlist.php"><i class="fas fa-angle-right"></i> รายการถูกใจ</a></li>
                    <?php else: ?>
                        <li><a href="login.php"><i class="fas fa-angle-right"></i> เข้าสู่ระบบ</a></li>
                        <li><a href="register.php"><i class="fas fa-angle-right"></i> สมัครสมาชิก</a></li>
                        <li><a href="courses.php"><i class="fas fa-angle-right"></i> เรียกดูคอร์ส</a></li>
                        <li><a href="about.php"><i class="fas fa-angle-right"></i> เกี่ยวกับเรา</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="footer-section">
                <h4 class="footer-title">ติดต่อเรา</h4>
                <ul class="footer-links">
                    <li><i class="fas fa-map-marker-alt"></i> ต.ตะนาวศรี อ.สวนผึ้ง จ.ราชบุรี</li>
                    <li><i class="fas fa-phone"></i> 098-280-9175</li>
                    <li><i class="fas fa-envelope"></i> Sitthidet.SR@gmail.com</li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2025 DevShop. All rights reserved.</p>
            <div class="payment-methods">
                <i class="fab fa-cc-visa"></i>
                <i class="fab fa-cc-mastercard"></i>
                <i class="fab fa-cc-paypal"></i>
                <i class="fas fa-mobile-alt"></i>
            </div>
        </div>
    </div>
</footer>