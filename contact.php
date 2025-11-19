<?php
session_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/cart_functions.php';

$message = '';
$error = '';

// ประมวลผลฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message_text = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message_text)) {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'รูปแบบอีเมลไม่ถูกต้อง';
    } else {
        // บันทึกข้อความลงฐานข้อมูล
        $name = clean_input($name);
        $email = clean_input($email);
        $subject = clean_input($subject);
        $message_text = clean_input($message_text);
        
        $sql = "INSERT INTO contact_messages (name, email, subject, message) 
                VALUES ('$name', '$email', '$subject', '$message_text')";
        
        if ($conn->query($sql)) {
            require_once 'includes/redirect_helper.php';
            redirect_self_with_message('message_sent');
        } else {
            $error = 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติดต่อเรา - DevShop</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/sweetalert-helper.js?v=<?php echo time(); ?>"></script>
    <link rel="stylesheet" href="assets/css/contact.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="contact-hero">
        <h1>ติดต่อเรา</h1>
        <p>มีคำถามหรือข้อสงสัย? เราพร้อมช่วยเหลือคุณ</p>
    </div>

    <div class="contact-content">
        <div class="contact-grid">
            <div class="contact-info">
                <h2>ข้อมูลติดต่อ</h2>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <h3>ที่อยู่</h3>
                        <p>175/2 หมู่ 3 ต.ตะนาวศรี<br>อ.สวนผึ้ง จ.ราชบุรี 70180</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="info-content">
                        <h3>โทรศัพท์</h3>
                        <p>098-280-9175</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="info-content">
                        <h3>อีเมล</h3>
                        <p>Sitthidet.SR@gmail.com</p>
                    </div>
                </div>

                <h3 style="margin-top: 30px; margin-bottom: 15px; color: #2c3e50;">ติดตามเราได้ที่</h3>
                <div class="social-links">
                    <a href="https://www.facebook.com/SitthidetSR/" target="_blank" class="social-link" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://www.instagram.com/sitthidet_jack/" target="_blank" class="social-link" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://www.youtube.com/@sitthidet-sr" target="_blank" class="social-link" title="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="https://line.me/ti/p/jack280645" target="_blank" class="social-link" title="LINE">
                        <i class="fab fa-line"></i>
                    </a>
                </div>
            </div>

            <div class="contact-form">
                <h2>ส่งข้อความถึงเรา</h2>



                <?php if ($error): ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด!',
                                text: '<?php echo addslashes($error); ?>',
                                confirmButtonColor: '#667eea',
                                confirmButtonText: 'ตกลง'
                            });
                        });
                    </script>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">ชื่อ-นามสกุล <span style="color: red;">*</span></label>
                        <input type="text" id="name" name="name" required
                               placeholder="กรอกชื่อของคุณ"
                               oninvalid="this.setCustomValidity('กรุณากรอกชื่อ-นามสกุล')"
                               oninput="this.setCustomValidity('')"
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">อีเมล <span style="color: red;">*</span></label>
                        <input type="email" id="email" name="email" required
                               placeholder="example@email.com"
                               oninvalid="this.setCustomValidity('กรุณากรอกอีเมลให้ถูกต้อง')"
                               oninput="this.setCustomValidity('')"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="subject">หัวข้อ <span style="color: red;">*</span></label>
                        <input type="text" id="subject" name="subject" required
                               placeholder="หัวข้อที่ต้องการติดต่อ"
                               oninvalid="this.setCustomValidity('กรุณากรอกหัวข้อ')"
                               oninput="this.setCustomValidity('')"
                               value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="message">ข้อความ <span style="color: red;">*</span></label>
                        <textarea id="message" name="message" required
                                  placeholder="รายละเอียดที่ต้องการติดต่อ"
                                  oninvalid="this.setCustomValidity('กรุณากรอกข้อความ')"
                                  oninput="this.setCustomValidity('')"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> ส่งข้อความ
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>
