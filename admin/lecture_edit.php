<?php
session_start();

ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
ini_set('max_execution_time', '300');
ini_set('max_input_time', '300');

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php';

$page_title = 'แก้ไขบทเรียน';

if (!isset($_GET['id'])) {
    header('Location: courses.php');
    exit;
}

$lecture_id = intval($_GET['id']);
$course_id = intval($_GET['course']);

// Handle delete video
if (isset($_POST['delete_video'])) {
    $check_sql = "SELECT content_url FROM lectures WHERE lecture_id = $lecture_id";
    $check_result = $conn->query($check_sql);
    if ($check_result && $row = $check_result->fetch_assoc()) {
        $content_url = $row['content_url'];
        if ($content_url && strpos($content_url, 'uploads/videos/') === 0) {
            $file_path = '../' . $content_url;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        $conn->query("UPDATE lectures SET content_url = NULL WHERE lecture_id = $lecture_id");
    }
    header('Location: lecture_edit.php?id=' . $lecture_id . '&course=' . $course_id . '&msg=video_deleted');
    exit;
}

$lecture_sql = "SELECT l.*, s.section_title, s.course_id 
                FROM lectures l 
                JOIN sections s ON l.section_id = s.section_id 
                WHERE l.lecture_id = $lecture_id";

$lecture_result = $conn->query($lecture_sql);

if (!$lecture_result) {
    die("เกิดข้อผิดพลาดฐานข้อมูล: " . $conn->error);
}

$lecture = $lecture_result->fetch_assoc();

if (!$lecture) {
    echo "<script>alert('ไม่พบบทเรียน ID: $lecture_id'); window.location.href='course_content.php?id=$course_id';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $lecture_title = isset($_POST['lecture_title']) ? trim($_POST['lecture_title']) : '';
        $lecture_type = isset($_POST['lecture_type']) ? $_POST['lecture_type'] : 'article';
        $content_text = isset($_POST['content_text']) ? trim($_POST['content_text']) : '';
        $duration_minutes = isset($_POST['duration_minutes']) && $_POST['duration_minutes'] !== '' ? intval($_POST['duration_minutes']) : 0;

        if (empty($lecture_title)) {
            $_SESSION['error'] = 'กรุณากรอกชื่อบทเรียน';
            header("Location: lecture_edit.php?id=$lecture_id&course=$course_id");
            exit;
        }

        $allowed_types = ['video', 'article', 'quiz', 'file'];
        if (!in_array($lecture_type, $allowed_types)) {
            $lecture_type = 'article';
        }

        $lecture_title = $conn->real_escape_string($lecture_title);
        $content_text = $conn->real_escape_string($content_text);

        $content_url = $lecture['content_url'];
        $upload_error = '';

        if ($lecture_type == 'video' && isset($_FILES['video_file']) && $_FILES['video_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['video_file'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $upload_errors = [
                    UPLOAD_ERR_INI_SIZE => 'ไฟล์ใหญ่เกินกำหนด',
                    UPLOAD_ERR_FORM_SIZE => 'ไฟล์ใหญ่เกินกำหนด',
                    UPLOAD_ERR_PARTIAL => 'อัพโหลดไม่สมบูรณ์',
                    UPLOAD_ERR_NO_TMP_DIR => 'ไม่พบโฟลเดอร์ temp',
                    UPLOAD_ERR_CANT_WRITE => 'ไม่สามารถเขียนไฟล์ได้',
                ];
                $upload_error = $upload_errors[$file['error']] ?? 'เกิดข้อผิดพลาดในการอัพโหลด';
            } else {
                $allowed_extensions = ['mp4', 'webm', 'ogg'];
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($extension, $allowed_extensions)) {
                    $upload_error = 'รองรับเฉพาะไฟล์ MP4, WebM, OGG เท่านั้น';
                } elseif ($file['size'] > 100 * 1024 * 1024) {
                    $upload_error = 'ไฟล์ใหญ่เกิน 100MB';
                } else {
                    // สร้างโฟลเดอร์ถ้ายังไม่มี
                    if (!file_exists('../uploads/videos')) {
                        mkdir('../uploads/videos', 0777, true);
                        chmod('../uploads/videos', 0777);
                    }

                    // ลบไฟล์เก่าถ้ามี
                    if ($content_url && strpos($content_url, 'uploads/videos/') === 0) {
                        $old_file = '../' . $content_url;
                        if (file_exists($old_file)) {
                            unlink($old_file);
                        }
                    }

                    // สร้างชื่อไฟล์ใหม่
                    $new_filename = 'video_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
                    $upload_path = '../uploads/videos/' . $new_filename;

                    // อัพโหลดไฟล์ใหม่
                    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                        chmod($upload_path, 0644);
                        $content_url = 'uploads/videos/' . $new_filename;
                    } else {
                        $upload_error = 'ไม่สามารถย้ายไฟล์ได้ ตรวจสอบ permissions ของโฟลเดอร์';
                    }
                }
            }
        }

        $sql = "UPDATE lectures SET 
                lecture_title = '$lecture_title',
                content_url = " . ($content_url ? "'$content_url'" : "NULL") . ",
                content_text = '$content_text',
                duration_minutes = $duration_minutes
                WHERE lecture_id = $lecture_id";

        $update_result = $conn->query($sql);

        if ($update_result) {
            if ($upload_error) {
                $_SESSION['error'] = 'บันทึกข้อมูลสำเร็จ แต่: ' . $upload_error;
            } else {
                $_SESSION['success'] = 'บันทึกการแก้ไขเรียบร้อยแล้ว';
            }
            header("Location: course_content.php?id=$course_id&msg=lecture_updated");
            exit;
        } else {
            $_SESSION['error'] = 'ไม่สามารถบันทึกข้อมูลได้: ' . $conn->error;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
}
?>

<?php include 'includes/header.php'; ?>

<?php if (isset($_SESSION['error'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด!',
                text: '<?php echo addslashes($_SESSION['error']); ?>',
                confirmButtonColor: '#dc3545'
            });
        });
    </script>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<link rel="stylesheet" href="../assets/css/admin-lecture-edit.css">

<div style="margin-bottom: 20px;">
    <h2><i class="fas fa-edit"></i> แก้ไขบทเรียน</h2>
    <p style="color: #7f8c8d;">หัวข้อ: <?php echo htmlspecialchars($lecture['section_title']); ?></p>
</div>

<div class="content-box">
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>ชื่อบทเรียน *</label>
            <input type="text" name="lecture_title" value="<?php echo htmlspecialchars($lecture['lecture_title']); ?>" required>
        </div>

        <div class="form-group">
            <label>ประเภท *</label>
            <select name="lecture_type" id="lecture_type" onchange="toggleFields()" required>
                <option value="video" <?php echo $lecture['lecture_type'] == 'video' ? 'selected' : ''; ?>>วิดีโอ</option>
                <option value="article" <?php echo $lecture['lecture_type'] == 'article' ? 'selected' : ''; ?>>บทความ</option>
            </select>
        </div>

        <div class="form-group" id="video_field">
            <label>ไฟล์วิดีโอ</label>
            <?php if ($lecture['content_url']): ?>
                <div class="video-preview" style="margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #28a745;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="flex: 1;">
                            <p style="color: #28a745; margin: 0 0 8px 0; font-weight: 600;">
                                <i class="fas fa-check-circle"></i> มีวิดีโออยู่แล้ว
                            </p>
                            <p style="color: #495057; margin: 0 0 5px 0; font-size: 14px;">
                                <i class="fas fa-file-video"></i> <?php echo basename($lecture['content_url']); ?>
                            </p>
                            <p style="color: #6c757d; margin: 0; font-size: 13px;">
                                <i class="fas fa-info-circle"></i> อัพโหลดไฟล์ใหม่ด้านล่างเพื่อแทนที่
                            </p>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <a href="../<?php echo htmlspecialchars($lecture['content_url']); ?>" target="_blank" class="btn-secondary" style="padding: 8px 16px; text-decoration: none; display: inline-block;">
                                <i class="fas fa-external-link-alt"></i> เปิดดู
                            </a>
                            <button type="button" onclick="deleteVideo()" class="btn-danger" style="padding: 8px 16px;">
                                <i class="fas fa-trash"></i> ลบ
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <p style="color: #6c757d; margin-bottom: 10px;">
                    <i class="fas fa-info-circle"></i> ยังไม่มีวิดีโอ กรุณาเลือกไฟล์เพื่ออัพโหลด
                </p>
            <?php endif; ?>
            <input type="file" name="video_file" accept="video/*">
            <small style="color: #7f8c8d;">รองรับ: MP4, WebM (ขนาดไม่เกิน 100MB)</small>
        </div>

        <div class="form-group">
            <label>เนื้อหา/คำอธิบาย</label>
            <textarea name="content_text" rows="8"><?php echo htmlspecialchars($lecture['content_text']); ?></textarea>
        </div>

        <div class="form-group">
            <label>ระยะเวลา (นาที)</label>
            <input type="number" name="duration_minutes" value="<?php echo $lecture['duration_minutes'] ?? ''; ?>" min="0">
        </div>

        <div style="margin-top: 30px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> บันทึกการแก้ไข
            </button>
            <a href="course_content.php?id=<?php echo $course_id; ?>" class="btn btn-secondary">
                <i class="fas fa-times"></i> ยกเลิก
            </a>
        </div>
    </form>
</div>

<script>
    function toggleFields() {
        const type = document.getElementById('lecture_type').value;
        const videoField = document.getElementById('video_field');

        if (type === 'video') {
            videoField.style.display = 'block';
        } else {
            videoField.style.display = 'none';
        }
    }

    toggleFields();

    function deleteVideo() {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: 'คุณต้องการลบวิดีโอนี้หรือไม่?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'ใช่, ลบเลย!',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="delete_video" value="1">';
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>

<?php include 'includes/footer.php'; ?>
