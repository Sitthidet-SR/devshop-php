<?php
session_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php';

$page_title = 'แก้ไขข้อมูลผู้ใช้';

// ตรวจสอบ user ID
if (!isset($_GET['id'])) {
    header('Location: users.php');
    exit;
}

$user_id = intval($_GET['id']);

// ดึงข้อมูลผู้ใช้
$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();

if (!$user) {
    header('Location: users.php');
    exit;
}

// อัพเดทข้อมูล
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = clean_input($_POST['first_name']);
    $last_name = clean_input($_POST['last_name']);
    $email = clean_input($_POST['email']);
    $phone = clean_input($_POST['phone']);
    $role = clean_input($_POST['role']);
    $status = clean_input($_POST['status']);
    
    // ตรวจสอบอีเมลซ้ำ
    $check_email = $conn->query("SELECT user_id FROM users WHERE email = '$email' AND user_id != $user_id");
    if ($check_email->num_rows > 0) {
        $error = "อีเมลนี้ถูกใช้งานแล้ว";
    } else {
        $sql = "UPDATE users SET 
                first_name = '$first_name',
                last_name = '$last_name',
                email = '$email',
                phone = '$phone',
                role = '$role',
                status = '$status'";
        
        // ถ้ามีการเปลี่ยนรหัสผ่าน
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $sql .= ", password = '$password'";
        }
        
        $sql .= " WHERE user_id = $user_id";
        
        if ($conn->query($sql)) {
            header('Location: users.php?msg=updated');
            exit;
        } else {
            $error = "เกิดข้อผิดพลาด: " . $conn->error;
        }
    }
}
?>

<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="../assets/css/admin-reviews.css">

<div class="content-box form-container">
    <a href="users.php" class="back-link">
        <i class="fas fa-arrow-left"></i> กลับไปรายการผู้ใช้
    </a>

    <h2><i class="fas fa-user-edit"></i> แก้ไขข้อมูลผู้ใช้</h2>

    <?php if (isset($error)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด!',
                    text: '<?php echo addslashes($error); ?>',
                    confirmButtonColor: '#667eea'
                });
            });
        </script>
    <?php endif; ?>

    <div class="form-section">
        <div class="form-section-title">
            <i class="fas fa-info-circle"></i> ข้อมูลผู้ใช้
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
            <div>
                <strong>User ID:</strong> <?php echo $user['user_id']; ?>
            </div>
            <div>
                <strong>สมัครเมื่อ:</strong> <?php echo date('d/m/Y H:i น.', strtotime($user['created_at'])); ?>
            </div>
        </div>
    </div>

    <form method="POST" action="">
        <div class="form-grid">
            <div class="form-group">
                <label for="first_name">ชื่อ <span style="color: red;">*</span></label>
                <input type="text" id="first_name" name="first_name" 
                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="last_name">นามสกุล <span style="color: red;">*</span></label>
                <input type="text" id="last_name" name="last_name" 
                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
            </div>
        </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> อีเมล <span style="color: red;">*</span></label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone"><i class="fas fa-phone"></i> เบอร์โทรศัพท์</label>
                <input type="text" id="phone" name="phone" 
                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                       placeholder="เช่น 0812345678">
            </div>

            <div class="form-group">
                <label for="role"><i class="fas fa-user-tag"></i> บทบาท <span style="color: red;">*</span></label>
                <select id="role" name="role" required>
                    <option value="student" <?php echo $user['role'] == 'student' ? 'selected' : ''; ?>>
                        👨‍🎓 Student (นักเรียน)
                    </option>
                    <option value="instructor" <?php echo $user['role'] == 'instructor' ? 'selected' : ''; ?>>
                        👨‍🏫 Instructor (ผู้สอน)
                    </option>
                    <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>
                        👑 Admin (ผู้ดูแลระบบ)
                    </option>
                </select>
            </div>
            <div class="form-group">
                <label for="status"><i class="fas fa-toggle-on"></i> สถานะ <span style="color: red;">*</span></label>
                <select id="status" name="status" required>
                    <option value="active" <?php echo $user['status'] == 'active' ? 'selected' : ''; ?>>
                        ✅ Active (ใช้งาน)
                    </option>
                    <option value="inactive" <?php echo $user['status'] == 'inactive' ? 'selected' : ''; ?>>
                        ⛔ Inactive (ระงับ)
                    </option>
                </select>
            </div>

            <div class="form-group full-width">
                <label for="password"><i class="fas fa-lock"></i> รหัสผ่านใหม่</label>
                <input type="password" id="password" name="password" 
                       placeholder="ใส่เฉพาะเมื่อต้องการเปลี่ยนรหัสผ่าน">
                <small style="color: #7f8c8d;">
                    <i class="fas fa-info-circle"></i> เว้นว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน
                </small>
            </div>
        </div>

        <div class="button-group">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> บันทึกการเปลี่ยนแปลง
            </button>
            <a href="users.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> ยกเลิก
            </a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
