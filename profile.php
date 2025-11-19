<?php
session_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth_functions.php';
require_once 'includes/cart_functions.php';

// ตรวจสอบว่า login หรือไม่
if (!is_logged_in()) {
    header('Location: login.php?redirect=profile.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// ดึงข้อมูลผู้ใช้
$sql = "SELECT * FROM users WHERE user_id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// อัพโหลดรูปโปรไฟล์
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_avatar'])) {
    require_once 'includes/upload_functions.php';

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_result = upload_image($_FILES['avatar'], 'users', 2097152); // 2MB

        if ($upload_result['success']) {
            // ลบรูปเดิม
            if (!empty($user['profile_image']) && strpos($user['profile_image'], 'uploads/') === 0) {
                delete_image($user['profile_image']);
            }

            $profile_image = $upload_result['path'];
            $update_sql = "UPDATE users SET profile_image = '$profile_image' WHERE user_id = $user_id";

            if ($conn->query($update_sql)) {
                require_once 'includes/redirect_helper.php';
                redirect_self_with_message('avatar_uploaded');
            } else {
                $error = 'เกิดข้อผิดพลาด: ' . $conn->error;
            }
        } else {
            $error = 'อัพโหลดรูปไม่สำเร็จ: ' . $upload_result['message'];
        }
    }
}

// ลบรูปโปรไฟล์
if (isset($_GET['delete_avatar'])) {
    if (!empty($user['profile_image']) && strpos($user['profile_image'], 'uploads/') === 0) {
        delete_image($user['profile_image']);
    }

    $update_sql = "UPDATE users SET profile_image = NULL WHERE user_id = $user_id";
    if ($conn->query($update_sql)) {
        require_once 'includes/redirect_helper.php';
        redirect_self_with_message('avatar_deleted');
    }
}

// อัพเดทโปรไฟล์
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = clean_input($_POST['first_name']);
    $last_name = clean_input($_POST['last_name']);
    $email = clean_input($_POST['email']);
    $phone = clean_input($_POST['phone']);
    $bio = clean_input($_POST['bio']);

    // ตรวจสอบอีเมลซ้ำ
    $check_email = $conn->query("SELECT user_id FROM users WHERE email = '$email' AND user_id != $user_id");
    if ($check_email->num_rows > 0) {
        $error = 'อีเมลนี้ถูกใช้งานแล้ว';
    } else {
        $update_sql = "UPDATE users SET 
                      first_name = '$first_name',
                      last_name = '$last_name',
                      email = '$email',
                      phone = '$phone',
                      bio = '$bio'
                      WHERE user_id = $user_id";

        if ($conn->query($update_sql)) {
            $_SESSION['user_name'] = $first_name . ' ' . $last_name;
            require_once 'includes/redirect_helper.php';
            redirect_self_with_message('profile_updated');
        } else {
            $error = 'เกิดข้อผิดพลาด: ' . $conn->error;
        }
    }
}

// เปลี่ยนรหัสผ่าน
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // ตรวจสอบรหัสผ่านปัจจุบัน
    if (!password_verify($current_password, $user['password'])) {
        $error = 'รหัสผ่านปัจจุบันไม่ถูกต้อง';
    } elseif ($new_password !== $confirm_password) {
        $error = 'รหัสผ่านใหม่ไม่ตรงกัน';
    } elseif (strlen($new_password) < 6) {
        $error = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET password = '$hashed_password' WHERE user_id = $user_id";

        if ($conn->query($update_sql)) {
            require_once 'includes/redirect_helper.php';
            redirect_self_with_message('password_changed');
        } else {
            $error = 'เกิดข้อผิดพลาด: ' . $conn->error;
        }
    }
}

// ดึงคอร์สที่ลงทะเบียน
$enrolled_courses_sql = "SELECT c.*, e.enrolled_at, e.progress,
                        CONCAT(u.first_name, ' ', u.last_name) as instructor_name
                        FROM enrollments e
                        JOIN courses c ON e.course_id = c.course_id
                        JOIN users u ON c.instructor_id = u.user_id
                        WHERE e.user_id = $user_id
                        ORDER BY e.enrolled_at DESC";
$enrolled_courses = $conn->query($enrolled_courses_sql)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์ - DevShop</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/sweetalert-helper.js?v=<?php echo time(); ?>"></script>
    <link rel="stylesheet" href="assets/css/profile.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>



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

    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                <?php if (!empty($user['profile_image'])): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                <?php else: ?>
                    <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                <?php endif; ?>
            </div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                <div class="profile-stats">
                    <div class="stat-item">
                        <i class="fas fa-book"></i>
                        <span><?php echo count($enrolled_courses); ?> คอร์ส</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-calendar"></i>
                        <span>สมัครเมื่อ <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="profile-content">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <ul class="profile-menu">
                    <li><a href="#profile" class="active" onclick="showTab('profile')"><i class="fas fa-user"></i> ข้อมูลส่วนตัว</a></li>
                    <li><a href="my_courses.php"><i class="fas fa-graduation-cap"></i> คอร์สของฉัน</a></li>
                    <li><a href="order_history.php"><i class="fas fa-receipt"></i> ประวัติการสั่งซื้อ</a></li>
                    <li><a href="#password" onclick="showTab('password')"><i class="fas fa-lock"></i> เปลี่ยนรหัสผ่าน</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a></li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="profile-main">
                <!-- Tab: Profile -->
                <div id="tab-profile" class="tab-content">
                    <h2 class="section-title"><i class="fas fa-user"></i> ข้อมูลส่วนตัว</h2>

                    <!-- Avatar Upload -->
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 12px; margin-bottom: 30px;">
                        <h3 style="font-size: 18px; margin-bottom: 15px;"><i class="fas fa-camera"></i> รูปโปรไฟล์</h3>
                        <div style="display: flex; align-items: center; gap: 20px;">
                            <div id="avatar-preview" style="width: 100px; height: 100px; border-radius: 50%; overflow: hidden; border: 3px solid #667eea;">
                                <?php if (!empty($user['profile_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <div style="width: 100%; height: 100%; background: #667eea; color: white; display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: bold;">
                                        <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div style="flex: 1;">
                                <form method="POST" enctype="multipart/form-data" id="avatar-form">
                                    <input type="file" name="avatar" id="avatar-input" accept="image/*" style="display: none;" onchange="previewAvatar(this)">
                                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                        <button type="button" onclick="document.getElementById('avatar-input').click()" class="btn-primary" style="padding: 10px 20px;">
                                            <i class="fas fa-upload"></i> เลือกรูปภาพ
                                        </button>
                                        <button type="submit" name="upload_avatar" class="btn-primary" style="padding: 10px 20px; background: #28a745; opacity: 0.5;" id="upload-btn" disabled>
                                            <i class="fas fa-save"></i> บันทึกรูป
                                        </button>
                                        <?php if (!empty($user['profile_image'])): ?>
                                            <a href="javascript:void(0)" onclick="confirmDelete('คุณต้องการลบรูปโปรไฟล์หรือไม่?', '?delete_avatar=1')" class="btn-primary" style="padding: 10px 20px; background: #dc3545; text-decoration: none;">
                                                <i class="fas fa-trash"></i> ลบรูป
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <small style="display: block; margin-top: 10px; color: #7f8c8d;">
                                        <i class="fas fa-info-circle"></i> รองรับ JPG, PNG, GIF, WebP (สูงสุด 2MB)
                                    </small>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label>ชื่อ *</label>
                                <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>นามสกุล *</label>
                                <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>อีเมล *</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>เบอร์โทรศัพท์</label>
                                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>เกี่ยวกับฉัน</label>
                            <textarea name="bio"><?php echo isset($user['bio']) ? htmlspecialchars($user['bio']) : ''; ?></textarea>
                        </div>
                        <button type="submit" name="update_profile" class="btn-primary">
                            <i class="fas fa-save"></i> บันทึกข้อมูล
                        </button>
                    </form>
                </div>

                <!-- Tab: Courses -->
                <div id="tab-courses" class="tab-content" style="display: none;">
                    <h2 class="section-title"><i class="fas fa-book"></i> คอร์สของฉัน (<?php echo count($enrolled_courses); ?>)</h2>
                    <?php if (!empty($enrolled_courses)): ?>
                        <div class="course-list">
                            <?php foreach ($enrolled_courses as $course): ?>
                                <div class="course-item">
                                    <img src="<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="<?php echo htmlspecialchars($course['course_title']); ?>" class="course-thumb">
                                    <div class="course-details">
                                        <h3><?php echo htmlspecialchars($course['course_title']); ?></h3>
                                        <div class="course-meta">
                                            <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($course['instructor_name']); ?></span>
                                            <span><i class="fas fa-calendar"></i> ลงทะเบียนเมื่อ <?php echo date('d/m/Y', strtotime($course['enrolled_at'])); ?></span>
                                        </div>
                                        <div>
                                            <small>ความคืบหน้า: <?php echo $course['progress']; ?>%</small>
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: <?php echo $course['progress']; ?>%"></div>
                                            </div>
                                        </div>
                                        <a href="course.php?slug=<?php echo $course['course_slug']; ?>" class="btn-primary" style="display: inline-block; margin-top: 10px; text-decoration: none;">
                                            <i class="fas fa-play"></i> เรียนต่อ
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-book-open"></i>
                            <h3>คุณยังไม่มีคอร์ส</h3>
                            <p>เริ่มเรียนรู้ด้วยการเลือกคอร์สที่คุณสนใจ</p>
                            <a href="courses.php" class="btn-primary">เลือกคอร์ส</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tab: Password -->
                <div id="tab-password" class="tab-content" style="display: none;">
                    <h2 class="section-title"><i class="fas fa-lock"></i> เปลี่ยนรหัสผ่าน</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label>รหัสผ่านปัจจุบัน *</label>
                            <input type="password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label>รหัสผ่านใหม่ *</label>
                            <input type="password" name="new_password" required minlength="6">
                            <small>รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร</small>
                        </div>
                        <div class="form-group">
                            <label>ยืนยันรหัสผ่านใหม่ *</label>
                            <input type="password" name="confirm_password" required minlength="6">
                        </div>
                        <button type="submit" name="change_password" class="btn-primary">
                            <i class="fas fa-key"></i> เปลี่ยนรหัสผ่าน
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];

                // ตรวจสอบขนาดไฟล์
                if (file.size > 2097152) {
                    Swal.fire({
                        icon: 'error',
                        title: 'ไฟล์ใหญ่เกินไป',
                        text: 'ไฟล์มีขนาด ' + (file.size / 1048576).toFixed(2) + ' MB กรุณาเลือกไฟล์ที่เล็กกว่า 2MB',
                        confirmButtonColor: '#667eea',
                        confirmButtonText: 'ตกลง'
                    });
                    input.value = '';
                    return;
                }

                // ตรวจสอบประเภทไฟล์
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'ประเภทไฟล์ไม่ถูกต้อง',
                        text: 'กรุณาเลือกไฟล์รูปภาพเท่านั้น (JPG, PNG, GIF, WebP)',
                        confirmButtonColor: '#667eea',
                        confirmButtonText: 'ตกลง'
                    });
                    input.value = '';
                    return;
                }

                // แสดง preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    const avatarPreview = document.getElementById('avatar-preview');
                    if (avatarPreview) {
                        avatarPreview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">';
                    }

                    // เปิดใช้งานปุ่มบันทึก
                    const uploadBtn = document.getElementById('upload-btn');
                    if (uploadBtn) {
                        uploadBtn.disabled = false;
                        uploadBtn.style.opacity = '1';
                    }
                };
                reader.readAsDataURL(file);
            }
        }

        function showTab(tabName) {
            // ซ่อนทุก tab
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.style.display = 'none';
            });

            // แสดง tab ที่เลือก
            document.getElementById('tab-' + tabName).style.display = 'block';

            // อัพเดท active menu
            document.querySelectorAll('.profile-menu a').forEach(link => {
                link.classList.remove('active');
            });
            event.target.closest('a').classList.add('active');

            // อัพเดท URL hash
            window.location.hash = tabName;

            return false;
        }

        // โหลด tab จาก URL hash
        window.addEventListener('DOMContentLoaded', function() {
            const hash = window.location.hash.substring(1);
            if (hash && ['profile', 'courses', 'password'].includes(hash)) {
                const link = document.querySelector(`a[href="#${hash}"]`);
                if (link) link.click();
            }
        });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>

</html>