<?php
session_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/category_functions.php';
require_once '../includes/upload_functions.php';
require_once 'includes/auth_check.php';

$page_title = 'เพิ่มคอร์สใหม่';

$message = '';
$error = '';

// ดึงหมวดหมู่และผู้สอน
$categories = get_all_categories();
$instructors = $conn->query("SELECT user_id, first_name, last_name FROM users WHERE role IN ('instructor', 'admin')")->fetch_all(MYSQLI_ASSOC);

// เพิ่มคอร์ส
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $instructor_id = intval($_POST['instructor_id']);
    $category_id = intval($_POST['category_id']);
    $title = clean_input($_POST['course_title']);
    $slug = clean_input($_POST['course_slug']);
    $description = clean_input($_POST['description']);
    $short_description = clean_input($_POST['short_description']);
    $level = clean_input($_POST['level']);
    $price = floatval($_POST['price']);
    $discount_price = !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null;
    $duration_hours = floatval($_POST['duration_hours']);
    $total_lectures = intval($_POST['total_lectures']);
    $status = clean_input($_POST['status']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $bestseller = isset($_POST['bestseller']) ? 1 : 0;
    
    // จัดการอัพโหลดรูปภาพ
    $thumbnail = '';
    
    // ตรวจสอบว่ามีการอัพโหลดไฟล์หรือไม่
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] !== UPLOAD_ERR_NO_FILE) {
        // มีการอัพโหลดไฟล์
        $upload_result = upload_image($_FILES['thumbnail'], 'courses');
        if ($upload_result['success']) {
            $thumbnail = $upload_result['path'];
            $message = 'อัพโหลดรูปภาพสำเร็จ: ' . $thumbnail;
        } else {
            $error = 'อัพโหลดรูปภาพไม่สำเร็จ: ' . $upload_result['message'];
        }
    } elseif (!empty($_POST['thumbnail_url'])) {
        // ใช้ URL ที่กรอกมา
        $thumbnail = clean_input($_POST['thumbnail_url']);
    } else {
        // ไม่มีรูปภาพ
        $error = 'กรุณาอัพโหลดรูปภาพหรือใส่ URL รูปภาพ';
    }

    // บันทึกลงฐานข้อมูลถ้าไม่มี error
    if (!$error && !empty($thumbnail)) {
        $sql = "INSERT INTO courses (
            instructor_id, category_id, course_title, course_slug, description, 
            short_description, thumbnail, level, price, discount_price, 
            duration_hours, total_lectures, status, featured, bestseller
        ) VALUES (
            $instructor_id, $category_id, '$title', '$slug', '$description',
            '$short_description', '$thumbnail', '$level', $price, " .
            ($discount_price ? $discount_price : 'NULL') . ",
            $duration_hours, $total_lectures, '$status', $featured, $bestseller
        )";

        if ($conn->query($sql)) {
            $_SESSION['success_message'] = 'เพิ่มคอร์สสำเร็จ!';
            header('Location: courses.php?msg=added');
            exit;
        } else {
            // ตรวจสอบ error แบบละเอียด
            if (strpos($conn->error, 'Duplicate entry') !== false && strpos($conn->error, 'course_slug') !== false) {
                $error = 'Course Slug ซ้ำ! กรุณาใช้ slug ที่ไม่ซ้ำกัน';
            } else {
                $error = 'บันทึกลงฐานข้อมูลไม่สำเร็จ: ' . $conn->error;
            }
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<link rel="stylesheet" href="../assets/css/admin-reviews.css">

<?php if ($error): ?>
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

<?php if ($message): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: '<?php echo addslashes($message); ?>',
                confirmButtonColor: '#667eea',
                timer: 2000,
                timerProgressBar: true
            });
        });
    </script>
<?php endif; ?>

<div class="content-box form-container">
    <a href="courses.php" class="back-link">
        <i class="fas fa-arrow-left"></i> กลับไปรายการคอร์ส
    </a>

    <h2><i class="fas fa-plus"></i> เพิ่มคอร์สใหม่</h2>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-grid">
            <div class="form-group">
                <label>ผู้สอน *</label>
                <select name="instructor_id" required>
                    <option value="">เลือกผู้สอน</option>
                    <?php foreach ($instructors as $instructor): ?>
                        <option value="<?php echo $instructor['user_id']; ?>">
                            <?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>หมวดหมู่ *</label>
                <select name="category_id" required>
                    <option value="">เลือกหมวดหมู่</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>">
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group full-width">
                <label>ชื่อคอร์ส *</label>
                <input type="text" name="course_title" required>
            </div>

            <div class="form-group full-width">
                <label>Slug (URL) *</label>
                <input type="text" name="course_slug" required>
                <small>ตัวอย่าง: html-css-beginner (ใช้ตัวอักษรภาษาอังกฤษและ - เท่านั้น)</small>
            </div>

            <div class="form-group full-width">
                <label>คำอธิบายสั้น *</label>
                <textarea name="short_description" required></textarea>
            </div>

            <div class="form-group full-width">
                <label>คำอธิบายเต็ม</label>
                <textarea name="description" rows="5"></textarea>
            </div>

            <div class="form-group full-width">
                <label>รูปภาพคอร์ส *</label>
                <div id="image-preview" style="margin-bottom: 15px; display: none; text-align: center;">
                    <img id="preview-img" src="" alt="Preview" 
                         style="max-width: 400px; max-height: 250px; border-radius: 8px; border: 3px solid #667eea; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <div id="preview-status" style="margin-top: 8px; color: #667eea; font-size: 13px;">
                        <i class="fas fa-check-circle"></i> ตัวอย่างรูปภาพ
                    </div>
                </div>
                <input type="file" name="thumbnail" accept="image/*" id="thumbnail-input" onchange="previewImage(this)">
                <small style="color: #7f8c8d;">
                    <i class="fas fa-info-circle"></i> อัพโหลดรูปภาพ (JPG, PNG, GIF, WebP - สูงสุด <?php echo ini_get('upload_max_filesize'); ?>)
                </small>
            </div>

            <div class="form-group full-width">
                <label>หรือใส่ URL รูปภาพ</label>
                <input type="text" name="thumbnail_url" id="thumbnail_url"
                    placeholder="https://example.com/image.jpg"
                    onchange="previewURL(this)">
                <small>ถ้าไม่อัพโหลดรูป สามารถใส่ URL รูปภาพได้</small>
            </div>

            <script>
                function previewImage(input) {
                    const preview = document.getElementById('image-preview');
                    const previewImg = document.getElementById('preview-img');
                    
                    if (input.files && input.files[0]) {
                        const file = input.files[0];
                        
                        // ตรวจสอบขนาดไฟล์ (100MB = 104857600 bytes)
                        if (file.size > 104857600) {
                            Swal.fire({
                                icon: 'error',
                                title: 'ไฟล์ใหญ่เกินไป',
                                text: 'ไฟล์มีขนาด ' + (file.size / 1048576).toFixed(2) + ' MB กรุณาเลือกไฟล์ที่เล็กกว่า 100MB',
                                confirmButtonColor: '#667eea'
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
                                confirmButtonColor: '#667eea'
                            });
                            input.value = '';
                            return;
                        }
                        
                        const reader = new FileReader();
                        const statusDiv = document.getElementById('preview-status');
                        
                        reader.onload = function(e) {
                            previewImg.src = e.target.result;
                            previewImg.style.display = 'block';
                            preview.style.display = 'block';
                            statusDiv.innerHTML = '<i class="fas fa-check-circle"></i> อัพโหลดรูปภาพสำเร็จ';
                            statusDiv.style.color = '#27ae60';
                        }
                        
                        reader.readAsDataURL(file);
                        
                        // ล้าง URL input
                        document.getElementById('thumbnail_url').value = '';
                    } else {
                        preview.style.display = 'none';
                    }
                }

                function previewURL(input) {
                    const preview = document.getElementById('image-preview');
                    const previewImg = document.getElementById('preview-img');
                    const statusDiv = document.getElementById('preview-status');
                    
                    if (input.value) {
                        // ล้าง file input
                        document.getElementById('thumbnail-input').value = '';
                        
                        // แสดง loading
                        statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังโหลด...';
                        statusDiv.style.color = '#667eea';
                        preview.style.display = 'block';
                        
                        // ตั้งค่า error handler
                        previewImg.onerror = function() {
                            statusDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ไม่สามารถโหลดรูปจาก URL นี้ได้';
                            statusDiv.style.color = '#e74c3c';
                            previewImg.style.display = 'none';
                        };
                        
                        // ตั้งค่า success handler
                        previewImg.onload = function() {
                            previewImg.style.display = 'block';
                            statusDiv.innerHTML = '<i class="fas fa-check-circle"></i> โหลดรูปภาพสำเร็จ';
                            statusDiv.style.color = '#27ae60';
                        };
                        
                        // โหลดรูป
                        previewImg.src = input.value;
                    } else {
                        preview.style.display = 'none';
                    }
                }

            </script>

            <div class="form-group">
                <label>ระดับความยาก *</label>
                <select name="level" required>
                    <option value="beginner">เริ่มต้น</option>
                    <option value="intermediate">กลาง</option>
                    <option value="advanced">ขั้นสูง</option>
                </select>
            </div>

            <div class="form-group">
                <label>สถานะ *</label>
                <select name="status" required>
                    <option value="draft">แบบร่าง</option>
                    <option value="published" selected>เผยแพร่</option>
                </select>
            </div>

            <div class="form-group">
                <label>ราคา (บาท) *</label>
                <input type="number" name="price" step="0.01" required>
            </div>

            <div class="form-group">
                <label>ราคาลด (บาท)</label>
                <input type="number" name="discount_price" step="0.01">
                <small>ถ้าไม่มีส่วนลดให้เว้นว่าง</small>
            </div>

            <div class="form-group">
                <label>ระยะเวลา (ชั่วโมง) *</label>
                <input type="number" name="duration_hours" step="0.5" required>
            </div>

            <div class="form-group">
                <label>จำนวนบทเรียน *</label>
                <input type="number" name="total_lectures" required>
            </div>

            <div class="form-group full-width">
                <label>ตัวเลือกเพิ่มเติม</label>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox" name="featured" value="1" id="featured">
                        <label for="featured">
                            <i class="fas fa-star"></i> คอร์สแนะนำ (Featured)
                        </label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="bestseller" value="1" id="bestseller">
                        <label for="bestseller">
                            <i class="fas fa-fire"></i> ขายดี (Bestseller)
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="button-group">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> บันทึกคอร์ส
            </button>
            <a href="courses.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> ยกเลิก
            </a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>