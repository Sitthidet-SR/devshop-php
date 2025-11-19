<?php
session_start();

require_once 'includes/config.php';
require_once 'includes/auth_functions.php';
require_once 'includes/functions.php';
require_once 'includes/cart_functions.php';

// ถ้า login แล้วให้ redirect ไปหน้าแรก
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

// ประมวลผลฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'กรุณากรอกอีเมลและรหัสผ่าน';
    } else {
        $result = login_user($email, $password);
        
        if ($result['success']) {
            $redirect = $_GET['redirect'] ?? 'index.php';
            $separator = strpos($redirect, '?') !== false ? '&' : '?';
            header('Location: ' . $redirect . $separator . 'msg=login');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - DevShop</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/sweetalert-helper.js?v=<?php echo time(); ?>"></script>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1><i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ</h1>
                <p>ยินดีต้อนรับกลับมา</p>
            </div>

            <?php if ($error): ?>
                <div id="php-alert-data" 
                     data-type="error" 
                     data-message="<?php echo htmlspecialchars($error); ?>" 
                     style="display:none;"></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">อีเมล</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="example@email.com"
                           oninvalid="this.setCustomValidity('กรุณากรอกอีเมลให้ถูกต้อง')"
                           oninput="this.setCustomValidity('')"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">รหัสผ่าน</label>
                    <input type="password" id="password" name="password" required
                           placeholder="กรอกรหัสผ่านของคุณ"
                           oninvalid="this.setCustomValidity('กรุณากรอกรหัสผ่าน')"
                           oninput="this.setCustomValidity('')">
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ
                </button>
            </form>

            <div class="auth-footer">
                ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิก</a>
            </div>

            <div class="demo-accounts">
                <h4><i class="fas fa-info-circle"></i> บัญชีทดสอบ</h4>
                <div class="demo-account-item">
                    <strong>Admin:</strong> admin@devshop.com
                    <span class="demo-password">รหัสผ่าน: 123456</span>
                </div>
                <div class="demo-account-item">
                    <strong>นักเรียน 1:</strong> user1@test.com
                    <span class="demo-password">รหัสผ่าน: 123456</span>
                </div>
                <div class="demo-account-item">
                    <strong>นักเรียน 2:</strong> user2@test.com
                    <span class="demo-password">รหัสผ่าน: 123456</span>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>
