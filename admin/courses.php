<?php
session_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/course_functions.php';
require_once 'includes/auth_check.php';

$page_title = 'จัดการคอร์ส';

// ลบคอร์ส
if (isset($_GET['delete'])) {
    $course_id = intval($_GET['delete']);
    $conn->query("DELETE FROM courses WHERE course_id = $course_id");
    header('Location: courses.php?msg=deleted');
    exit;
}

// เปลี่ยนสถานะคอร์ส
if (isset($_GET['toggle_status'])) {
    $course_id = intval($_GET['toggle_status']);
    $conn->query("UPDATE courses SET status = IF(status = 'published', 'draft', 'published') WHERE course_id = $course_id");
    header('Location: courses.php?msg=status_changed');
    exit;
}

// ดึงคอร์สทั้งหมด (รวมทุก status)
$courses = get_all_courses_admin();

// กรองตาม status ถ้ามี
if (isset($_GET['status'])) {
    $filter_status = $_GET['status'];
    $courses = array_filter($courses, function($course) use ($filter_status) {
        return $course['status'] == $filter_status;
    });
}
?>

<?php include 'includes/header.php'; ?>

<link rel="stylesheet" href="../assets/css/admin-reviews.css">

<div class="content-box">
    <?php if (isset($_GET['msg'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                <?php
                $messages = [
                    'added' => 'เพิ่มคอร์สเรียบร้อยแล้ว',
                    'updated' => 'แก้ไขคอร์สเรียบร้อยแล้ว',
                    'deleted' => 'ลบคอร์สเรียบร้อยแล้ว',
                    'status_changed' => 'เปลี่ยนสถานะคอร์สเรียบร้อยแล้ว'
                ];
                $msg = $_GET['msg'];
                if (isset($messages[$msg])):
                ?>
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ!',
                    text: '<?php echo $messages[$msg]; ?>',
                    confirmButtonColor: '#667eea',
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => {
                    // ลบ query string ออกจาก URL
                    window.history.replaceState({}, document.title, 'courses.php<?php echo isset($_GET['status']) ? '?status=' . $_GET['status'] : ''; ?>');
                });
                <?php endif; ?>
            });
        </script>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><i class="fas fa-book"></i> จัดการคอร์ส</h2>
        <a href="course_add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> เพิ่มคอร์สใหม่
        </a>
    </div>

    <div class="filter-tabs" style="margin-bottom: 20px;">
        <a href="courses.php" class="filter-tab <?php echo !isset($_GET['status']) ? 'active' : ''; ?>">
            ทั้งหมด <span class="badge"><?php echo count(get_all_courses_admin()); ?></span>
        </a>
        <a href="?status=published" class="filter-tab <?php echo isset($_GET['status']) && $_GET['status'] == 'published' ? 'active' : ''; ?>">
            <i class="fas fa-check-circle"></i> เผยแพร่ 
            <span class="badge badge-success">
                <?php echo count(array_filter(get_all_courses_admin(), function($c) { return $c['status'] == 'published'; })); ?>
            </span>
        </a>
        <a href="?status=draft" class="filter-tab <?php echo isset($_GET['status']) && $_GET['status'] == 'draft' ? 'active' : ''; ?>">
            <i class="fas fa-clock"></i> แบบร่าง 
            <span class="badge badge-warning">
                <?php echo count(array_filter(get_all_courses_admin(), function($c) { return $c['status'] == 'draft'; })); ?>
            </span>
        </a>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>รูปภาพ</th>
                    <th>ชื่อคอร์ส</th>
                    <th>ผู้สอน</th>
                    <th>หมวดหมู่</th>
                    <th>ราคา</th>
                    <th>สถานะ</th>
                    <th>ป้ายกำกับ</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                    <tr>
                        <td>
                            <img src="../<?php echo htmlspecialchars($course['thumbnail']); ?>" 
                                 alt="<?php echo htmlspecialchars($course['course_title']); ?>"
                                 style="width: 80px; height: 50px; object-fit: cover; border-radius: 5px;">
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($course['course_title']); ?></strong><br>
                            <small><?php echo htmlspecialchars($course['level']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($course['instructor_name']); ?></td>
                        <td><?php echo htmlspecialchars($course['category_name']); ?></td>
                        <td>
                            <?php if ($course['discount_price']): ?>
                                <s>฿<?php echo number_format($course['price']); ?></s><br>
                                <strong>฿<?php echo number_format($course['discount_price']); ?></strong>
                            <?php else: ?>
                                ฿<?php echo number_format($course['price']); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($course['status'] == 'published'): ?>
                                <span class="badge badge-success">
                                    <i class="fas fa-check-circle"></i> เผยแพร่
                                </span>
                            <?php else: ?>
                                <span class="badge badge-warning">
                                    <i class="fas fa-clock"></i> แบบร่าง
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($course['featured']): ?>
                                <span class="badge badge-info" style="margin-right: 5px;">
                                    <i class="fas fa-star"></i> แนะนำ
                                </span>
                            <?php endif; ?>
                            <?php if ($course['bestseller']): ?>
                                <span class="badge badge-danger">
                                    <i class="fas fa-fire"></i> ขายดี
                                </span>
                            <?php endif; ?>
                            <?php if (!$course['featured'] && !$course['bestseller']): ?>
                                <span style="color: #999;">-</span>
                            <?php endif; ?>
                        </td>
                        <td style="white-space: nowrap;">
                            <a href="course_content.php?id=<?php echo $course['course_id']; ?>" 
                               class="btn-sm btn-success"
                               title="จัดการเนื้อหา">
                                <i class="fas fa-book-open"></i> เนื้อหา
                            </a>
                            <a href="course_edit.php?id=<?php echo $course['course_id']; ?>" 
                               class="btn-sm btn-warning"
                               title="แก้ไข">
                                <i class="fas fa-edit"></i> แก้ไข
                            </a>
                            <a href="javascript:void(0)" 
                               class="btn-sm btn-danger"
                               onclick="confirmDelete('คุณต้องการลบคอร์สนี้หรือไม่?', '?delete=<?php echo $course['course_id']; ?>')"
                               title="ลบ">
                                <i class="fas fa-trash"></i> ลบ
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
