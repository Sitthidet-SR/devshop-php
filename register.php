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
    $confirm_password = $_POST['confirm_password'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'รูปแบบอีเมลไม่ถูกต้อง';
    } elseif (strlen($password) < 6) {
        $error = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
    } elseif ($password !== $confirm_password) {
        $error = 'รหัสผ่านไม่ตรงกัน';
    } else {
        // สมัครสมาชิก
        $result = register_user($email, $password, $first_name, $last_name, $phone);

        if ($result['success']) {
            $success = $result['message'];
            login_user($email, $password);
            header('Refresh: 2; URL=index.php');
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
    <title>สมัครสมาชิก - DevShop</title>
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
                <h1><i class="fas fa-user-plus"></i> สมัครสมาชิก</h1>
                <p>สร้างบัญชีเพื่อเริ่มเรียนรู้</p>
            </div>

            <?php if ($error): ?>
                <div id="php-alert-data" 
                     data-type="error" 
                     data-message="<?php echo htmlspecialchars($error); ?>" 
                     style="display:none;"></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ!',
                            text: '<?php echo htmlspecialchars($success); ?> กำลังนำคุณไปหน้าแรก...',
                            confirmButtonColor: '#667eea',
                            confirmButtonText: 'ตกลง',
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
                    });
                </script>
            <?php endif; ?>

            <form method="POST" action="" id="registerForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">ชื่อ <span style="color: red;">*</span></label>
                        <input type="text" id="first_name" name="first_name" required
                            placeholder="ชื่อของคุณ"
                            oninvalid="this.setCustomValidity('กรุณากรอกชื่อ')"
                            oninput="this.setCustomValidity('')"
                            value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="last_name">นามสกุล <span style="color: red;">*</span></label>
                        <input type="text" id="last_name" name="last_name" required
                            placeholder="นามสกุลของคุณ"
                            oninvalid="this.setCustomValidity('กรุณากรอกนามสกุล')"
                            oninput="this.setCustomValidity('')"
                            value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                    </div>
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
                    <label for="phone">เบอร์โทรศัพท์</label>
                    <input type="tel" id="phone" name="phone"
                        placeholder="0812345678"
                        pattern="[0-9]{9,10}"
                        oninvalid="this.setCustomValidity('กรุณากรอกเบอร์โทรศัพท์ 9-10 หลัก')"
                        oninput="this.setCustomValidity('')"
                        value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">รหัสผ่าน <span style="color: red;">*</span></label>
                    <input type="password" id="password" name="password" required minlength="6"
                        placeholder="อย่างน้อย 6 ตัวอักษร"
                        oninvalid="this.setCustomValidity('กรุณากรอกรหัสผ่านอย่างน้อย 6 ตัวอักษร')"
                        oninput="this.setCustomValidity('')">
                    <small style="color: #7f8c8d; font-size: 13px;">
                        <i class="fas fa-info-circle"></i> รหัสผ่านควรมีความยาวอย่างน้อย 6 ตัวอักษร
                    </small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">ยืนยันรหัสผ่าน <span style="color: red;">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6"
                        placeholder="กรอกรหัสผ่านอีกครั้ง"
                        oninvalid="this.setCustomValidity('กรุณายืนยันรหัสผ่าน')"
                        oninput="this.setCustomValidity('')">
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-user-plus"></i> สมัครสมาชิก
                </button>
            </form>

            <div class="auth-footer">
                มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a>
            </div>
        </div>
    </div>

    <script>
        // ตรวจสอบรหัสผ่านตรงกันหรือไม่
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            var password = document.getElementById('password').value;
            var confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                document.getElementById('confirm_password').setCustomValidity('รหัสผ่านไม่ตรงกัน');
                document.getElementById('confirm_password').reportValidity();
            }
        });

        document.getElementById('confirm_password').addEventListener('input', function() {
            this.setCustomValidity('');
        });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>

</html>