<?php
session_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php';

$page_title = 'จัดการเนื้อหาคอร์ส';

// ตรวจสอบ course_id
if (!isset($_GET['id'])) {
    header('Location: courses.php');
    exit;
}

$course_id = intval($_GET['id']);

// ดึงข้อมูลคอร์ส
$course = $conn->query("SELECT * FROM courses WHERE course_id = $course_id")->fetch_assoc();

if (!$course) {
    header('Location: courses.php');
    exit;
}

// ดึง sections และ lectures
$sections_sql = "SELECT * FROM sections WHERE course_id = $course_id ORDER BY section_order ASC";
$sections = $conn->query($sections_sql)->fetch_all(MYSQLI_ASSOC);

foreach ($sections as &$section) {
    $section_id = $section['section_id'];
    $lectures_sql = "SELECT * FROM lectures WHERE section_id = $section_id ORDER BY lecture_order ASC";
    $section['lectures'] = $conn->query($lectures_sql)->fetch_all(MYSQLI_ASSOC);
}

$message = '';
$error = '';

// จัดการ actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_section'])) {
        $section_title = clean_input($_POST['section_title']);
        $section_order = intval($_POST['section_order']);
        
        $sql = "INSERT INTO sections (course_id, section_title, section_order) 
                VALUES ($course_id, '$section_title', $section_order)";
        
        if ($conn->query($sql)) {
            header("Location: course_content.php?id=$course_id&msg=section_added");
            exit;
        }
    }
}

// ลบ section
if (isset($_GET['delete_section'])) {
    $section_id = intval($_GET['delete_section']);
    $conn->query("DELETE FROM sections WHERE section_id = $section_id");
    header("Location: course_content.php?id=$course_id&msg=section_deleted");
    exit;
}

// ลบ lecture
if (isset($_GET['delete_lecture'])) {
    $lecture_id = intval($_GET['delete_lecture']);
    
    // ลบไฟล์วิดีโอถ้ามี
    $lecture = $conn->query("SELECT content_url FROM lectures WHERE lecture_id = $lecture_id")->fetch_assoc();
    if ($lecture && $lecture['content_url'] && file_exists('../' . $lecture['content_url'])) {
        unlink('../' . $lecture['content_url']);
    }
    
    $conn->query("DELETE FROM lectures WHERE lecture_id = $lecture_id");
    header("Location: course_content.php?id=$course_id&msg=lecture_deleted");
    exit;
}
?>

<?php include 'includes/header.php'; ?>

<link rel="stylesheet" href="../assets/css/admin-reviews.css">

<?php if (isset($_GET['msg'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const messages = {
                'section_added': 'เพิ่มหัวข้อเรียบร้อยแล้ว',
                'section_deleted': 'ลบหัวข้อเรียบร้อยแล้ว',
                'lecture_added': 'เพิ่มบทเรียนเรียบร้อยแล้ว',
                'lecture_updated': 'แก้ไขบทเรียนเรียบร้อยแล้ว',
                'lecture_deleted': 'ลบบทเรียนเรียบร้อยแล้ว'
            };
            
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: messages['<?php echo $_GET['msg']; ?>'],
                confirmButtonColor: '#667eea',
                timer: 2000
            });
        });
    </script>
<?php endif; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2><i class="fas fa-book-open"></i> จัดการเนื้อหาคอร์ส</h2>
        <p style="color: #7f8c8d; margin-top: 5px;"><?php echo htmlspecialchars($course['course_title']); ?></p>
    </div>
    <div>
        <a href="courses.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> กลับ
        </a>
        <button onclick="openAddSectionModal()" class="btn btn-primary">
            <i class="fas fa-plus"></i> เพิ่มหัวข้อ
        </button>
    </div>
</div>

<div class="content-box">
    <?php if (empty($sections)): ?>
        <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h3>ยังไม่มีเนื้อหาบทเรียน</h3>
            <p>เริ่มต้นสร้างเนื้อหาคอร์สโดยการเพิ่มหัวข้อ (Section)</p>
            <button onclick="openAddSectionModal()" class="btn btn-primary" style="margin-top: 15px;">
                <i class="fas fa-plus"></i> เพิ่มหัวข้อแรก
            </button>
        </div>
    <?php else: ?>
        <?php foreach ($sections as $section): ?>
            <div class="section-card">
                <div class="section-header">
                    <div>
                        <span class="section-title">
                            <i class="fas fa-folder"></i> <?php echo htmlspecialchars($section['section_title']); ?>
                        </span>
                        <span style="color: #7f8c8d; font-size: 13px; margin-left: 10px;">
                            (<?php echo count($section['lectures']); ?> บทเรียน)
                        </span>
                    </div>
                    <div>
                        <button onclick="openAddLectureModal(<?php echo $section['section_id']; ?>, '<?php echo htmlspecialchars(addslashes($section['section_title'])); ?>')" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> เพิ่มบทเรียน
                        </button>
                        <a href="javascript:void(0)" onclick="confirmDelete('คุณต้องการลบหัวข้อนี้หรือไม่?', '?id=<?php echo $course_id; ?>&delete_section=<?php echo $section['section_id']; ?>')" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
                
                <div class="lecture-list">
                    <?php if (empty($section['lectures'])): ?>
                        <p style="text-align: center; color: #7f8c8d; padding: 20px;">
                            ยังไม่มีบทเรียนในหัวข้อนี้
                        </p>
                    <?php else: ?>
                        <?php foreach ($section['lectures'] as $lecture): ?>
                            <div class="lecture-item">
                                <div class="lecture-icon">
                                    <i class="fas fa-<?php echo $lecture['lecture_type'] == 'video' ? 'play' : 'file-alt'; ?>"></i>
                                </div>
                                <div class="lecture-info">
                                    <div class="lecture-name"><?php echo htmlspecialchars($lecture['lecture_title']); ?></div>
                                    <div class="lecture-meta">
                                        <?php 
                                        $types = ['video' => 'วิดีโอ', 'article' => 'บทความ', 'quiz' => 'แบบทดสอบ', 'file' => 'ไฟล์'];
                                        echo $types[$lecture['lecture_type']];
                                        ?>
                                        <?php if ($lecture['duration_minutes']): ?>
                                            • <?php echo $lecture['duration_minutes']; ?> นาที
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div>
                                    <a href="lecture_edit.php?id=<?php echo $lecture['lecture_id']; ?>&course=<?php echo $course_id; ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> แก้ไข
                                    </a>
                                    <a href="javascript:void(0)" onclick="confirmDelete('คุณต้องการลบบทเรียนนี้หรือไม่?', '?id=<?php echo $course_id; ?>&delete_lecture=<?php echo $lecture['lecture_id']; ?>')" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Add Section Modal -->
<div id="addSectionModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; width: 90%; max-width: 500px; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
        <div style="padding: 20px; border-bottom: 2px solid #ecf0f1; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0;"><i class="fas fa-folder-plus"></i> เพิ่มหัวข้อใหม่</h3>
            <button onclick="closeAddSectionModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" style="padding: 20px;">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">ชื่อหัวข้อ:</label>
                <input type="text" name="section_title" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">ลำดับ:</label>
                <input type="number" name="section_order" value="<?php echo count($sections) + 1; ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeAddSectionModal()" class="btn btn-secondary">ยกเลิก</button>
                <button type="submit" name="add_section" class="btn btn-primary">เพิ่มหัวข้อ</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Lecture Modal -->
<div id="addLectureModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
        <div style="padding: 20px; border-bottom: 2px solid #ecf0f1; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0;"><i class="fas fa-plus-circle"></i> เพิ่มบทเรียนใหม่</h3>
            <button onclick="closeAddLectureModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
        </div>
        <form action="lecture_add.php" method="POST" enctype="multipart/form-data" style="padding: 20px;">
            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
            <input type="hidden" name="section_id" id="lecture_section_id">
            
            <p style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                หัวข้อ: <strong id="section_name_display"></strong>
            </p>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">ชื่อบทเรียน:</label>
                <input type="text" name="lecture_title" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">ประเภท:</label>
                <select name="lecture_type" id="lecture_type" onchange="toggleLectureFields()" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="video">วิดีโอ</option>
                    <option value="article">บทความ</option>
                </select>
            </div>
            
            <div id="video_field" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">ไฟล์วิดีโอ:</label>
                <input type="file" name="video_file" accept="video/*" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                <small style="color: #7f8c8d;">รองรับ: MP4, WebM (ขนาดไม่เกิน 100MB)</small>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">เนื้อหา/คำอธิบาย:</label>
                <textarea name="content_text" rows="5" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"></textarea>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">ระยะเวลา (นาที):</label>
                <input type="number" name="duration_minutes" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeAddLectureModal()" class="btn btn-secondary">ยกเลิก</button>
                <button type="submit" class="btn btn-success">เพิ่มบทเรียน</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddSectionModal() {
        document.getElementById('addSectionModal').style.display = 'flex';
    }

    function closeAddSectionModal() {
        document.getElementById('addSectionModal').style.display = 'none';
    }

    function openAddLectureModal(sectionId, sectionName) {
        document.getElementById('lecture_section_id').value = sectionId;
        document.getElementById('section_name_display').textContent = sectionName;
        document.getElementById('addLectureModal').style.display = 'flex';
    }

    function closeAddLectureModal() {
        document.getElementById('addLectureModal').style.display = 'none';
    }

    function toggleLectureFields() {
        const type = document.getElementById('lecture_type').value;
        const videoField = document.getElementById('video_field');
        
        if (type === 'video') {
            videoField.style.display = 'block';
        } else {
            videoField.style.display = 'none';
        }
    }

    // ปิด modal เมื่อคลิกนอก
    document.getElementById('addSectionModal').addEventListener('click', function(e) {
        if (e.target === this) closeAddSectionModal();
    });

    document.getElementById('addLectureModal').addEventListener('click', function(e) {
        if (e.target === this) closeAddLectureModal();
    });
</script>

<?php include 'includes/footer.php'; ?>
