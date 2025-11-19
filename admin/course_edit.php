<?php
session_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/category_functions.php';
require_once '../includes/course_functions.php';
require_once '../includes/upload_functions.php';
require_once 'includes/auth_check.php';

$page_title = 'แก้ไขคอร์ส';

// ตรวจสอบ ID
if (!isset($_GET['id'])) {
    header('Location: courses.php');
    exit;
}

$course_id = intval($_GET['id']);
$course = get_course_by_id_admin($course_id);

if (!$course) {
    header('Location: courses.php');
    exit;
}

$message = '';
$error = '';

// ดึงหมวดหมู่และผู้สอน
$categories = get_all_categories();
$instructors = $conn->query("SELECT user_id, first_name, last_name FROM users WHERE role IN ('instructor', 'admin')")->fetch_all(MYSQLI_ASSOC);

// แก้ไขคอร์ส
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
    $thumbnail = $course['thumbnail']; // ใช้รูปเดิม
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_result = upload_image($_FILES['thumbnail'], 'courses');
        if ($upload_result['success']) {
            // ลบรูปเดิม
            if (!empty($course['thumbnail']) && strpos($course['thumbnail'], 'uploads/') === 0) {
                delete_image($course['thumbnail']);
            }
            $thumbnail = $upload_result['path'];
        } else {
            $error = $upload_result['message'];
        }
    } elseif (!empty($_POST['thumbnail_url'])) {
        $thumbnail = clean_input($_POST['thumbnail_url']);
    }
    
    $sql = "UPDATE courses SET
        instructor_id = $instructor_id,
        category_id = $category_id,
        course_title = '$title',
        course_slug = '$slug',
        description = '$description',
        short_description = '$short_description',
        thumbnail = '$thumbnail',
        level = '$level',
        price = $price,
        discount_price = " . ($discount_price ? $discount_price : 'NULL') . ",
        duration_hours = $duration_hours,
        total_lectures = $total_lectures,
        status = '$status',
        featured = $featured,
        bestseller = $bestseller
        WHERE course_id = $course_id";
    
    if ($conn->query($sql)) {
        header('Location: courses.php?msg=updated');
        exit;
    } else {
        $error = 'เกิดข้อผิดพลาด: ' . $conn->error;
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

<div class="content-box form-container">
    <a href="courses.php" class="back-link">
        <i class="fas fa-arrow-left"></i> กลับไปรายการคอร์ส
    </a>

    <h2><i class="fas fa-edit"></i> แก้ไขคอร์ส</h2>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="form-grid">
            <div class="form-group">
                <label>ผู้สอน *</label>
                <select name="instructor_id" required>
                    <?php foreach ($instructors as $instructor): ?>
                        <option value="<?php echo $instructor['user_id']; ?>"
                                <?php echo $course['instructor_id'] == $instructor['user_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>หมวดหมู่ *</label>
                <select name="category_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>"
                                <?php echo $course['category_id'] == $category['category_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group full-width">
                <label>ชื่อคอร์ส *</label>
                <input type="text" name="course_title" required 
                       value="<?php echo htmlspecialchars($course['course_title']); ?>">
            </div>

            <div class="form-group full-width">
                <label>Slug (URL) *</label>
                <input type="text" name="course_slug" required 
                       value="<?php echo htmlspecialchars($course['course_slug']); ?>">
            </div>

            <div class="form-group full-width">
                <label>คำอธิบายสั้น *</label>
                <textarea name="short_description" required><?php echo htmlspecialchars($course['short_description']); ?></textarea>
            </div>

            <div class="form-group full-width">
                <label>คำอธิบายเต็ม</label>
                <textarea name="description" rows="5"><?php echo htmlspecialchars($course['description']); ?></textarea>
            </div>

            <div class="form-group full-width">
                <label>รูปภาพคอร์ส</label>
                <div id="image-preview" style="margin-bottom: 15px; text-align: center;">
                    <img id="preview-img" src="../<?php echo htmlspecialchars($course['thumbnail']); ?>" 
                         alt="Preview" style="max-width: 400px; max-height: 250px; border-radius: 8px; border: 3px solid #667eea; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <div style="margin-top: 8px; color: #667eea; font-size: 13px;">
                        <i class="fas fa-image"></i> รูปภาพปัจจุบัน
                    </div>
                </div>
                <input type="file" name="thumbnail" accept="image/*" id="thumbnail-input" onchange="previewImage(this)">
                <small style="color: #e74c3c; font-weight: 500;">
                    <i class="fas fa-info-circle"></i> อัพโหลดรูปใหม่ (JPG, PNG, GIF, WebP - สูงสุด 2MB)
                </small>
            </div>

            <div class="form-group full-width">
                <label>หรือใส่ URL รูปภาพใหม่</label>
                <input type="text" name="thumbnail_url" id="thumbnail_url"
                       placeholder="<?php echo htmlspecialchars($course['thumbnail']); ?>"
                       onchange="previewURL(this)">
                <small>ถ้าไม่เปลี่ยนรูป ให้เว้นว่างไว้</small>
            </div>

            <script>
                function previewImage(input) {
                    const preview = document.getElementById('image-preview');
                    const previewImg = document.getElementById('preview-img');
                    
                    if (input.files && input.files[0]) {
                        const file = input.files[0];
                        
                        // ตรวจสอบขนาดไฟล์ (2MB = 2097152 bytes)
                        if (file.size > 2097152) {
                            Swal.fire({
                                icon: 'error',
                                title: 'ไฟล์ใหญ่เกินไป',
                                text: 'ไฟล์มีขนาด ' + (file.size / 1048576).toFixed(2) + ' MB กรุณาเลือกไฟล์ที่เล็กกว่า 2MB',
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
                        
                        reader.onload = function(e) {
                            previewImg.src = e.target.result;
                            preview.querySelector('div').innerHTML = '<i class="fas fa-check-circle"></i> รูปภาพใหม่ (ยังไม่บันทึก)';
                        }
                        
                        reader.readAsDataURL(file);
                        
                        // ล้าง URL input
                        document.getElementById('thumbnail_url').value = '';
                    }
                }

                function previewURL(input) {
                    const preview = document.getElementById('image-preview');
                    const previewImg = document.getElementById('preview-img');
                    const statusDiv = preview.querySelector('div');
                    
                    if (input.value) {
                        // ล้าง file input
                        document.getElementById('thumbnail-input').value = '';
                        
                        // ตั้งค่า error handler ก่อน
                        previewImg.onerror = function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'โหลดรูปภาพไม่สำเร็จ',
                                text: 'ไม่สามารถโหลดรูปภาพจาก URL นี้ได้ กรุณาตรวจสอบ URL หรืออัพโหลดไฟล์แทน',
                                confirmButtonColor: '#667eea'
                            });
                            previewImg.src = '../<?php echo htmlspecialchars($course['thumbnail']); ?>';
                            statusDiv.innerHTML = '<i class="fas fa-image"></i> รูปภาพปัจจุบัน';
                        };
                        
                        // ตั้งค่า success handler
                        previewImg.onload = function() {
                            statusDiv.innerHTML = '<i class="fas fa-check-circle"></i> รูปภาพใหม่จาก URL (ยังไม่บันทึก)';
                        };
                        
                        // โหลดรูป
                        previewImg.src = input.value;
                    }
                }
            </script>

            <div class="form-group">
                <label>ระดับความยาก *</label>
                <select name="level" required>
                    <option value="beginner" <?php echo $course['level'] == 'beginner' ? 'selected' : ''; ?>>เริ่มต้น</option>
                    <option value="intermediate" <?php echo $course['level'] == 'intermediate' ? 'selected' : ''; ?>>กลาง</option>
                    <option value="advanced" <?php echo $course['level'] == 'advanced' ? 'selected' : ''; ?>>ขั้นสูง</option>
                </select>
            </div>

            <div class="form-group">
                <label>สถานะ *</label>
                <select name="status" required>
                    <option value="draft" <?php echo $course['status'] == 'draft' ? 'selected' : ''; ?>>แบบร่าง</option>
                    <option value="published" <?php echo $course['status'] == 'published' ? 'selected' : ''; ?>>เผยแพร่</option>
                </select>
            </div>

            <div class="form-group">
                <label>ราคา (บาท) *</label>
                <input type="number" name="price" step="0.01" required 
                       value="<?php echo $course['price']; ?>">
            </div>

            <div class="form-group">
                <label>ราคาลด (บาท)</label>
                <input type="number" name="discount_price" step="0.01" 
                       value="<?php echo $course['discount_price']; ?>">
            </div>

            <div class="form-group">
                <label>ระยะเวลา (ชั่วโมง) *</label>
                <input type="number" name="duration_hours" step="0.5" required 
                       value="<?php echo $course['duration_hours']; ?>">
            </div>

            <div class="form-group">
                <label>จำนวนบทเรียน *</label>
                <input type="number" name="total_lectures" required 
                       value="<?php echo $course['total_lectures']; ?>">
            </div>

            <div class="form-group full-width">
                <label>ตัวเลือกเพิ่มเติม</label>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox" name="featured" value="1" id="featured"
                               <?php echo $course['featured'] ? 'checked' : ''; ?>>
                        <label for="featured">
                            <i class="fas fa-star"></i> คอร์สแนะนำ (Featured)
                        </label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="bestseller" value="1" id="bestseller"
                               <?php echo $course['bestseller'] ? 'checked' : ''; ?>>
                        <label for="bestseller">
                            <i class="fas fa-fire"></i> ขายดี (Bestseller)
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="button-group">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> บันทึกการแก้ไข
            </button>
            <a href="courses.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> ยกเลิก
            </a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
