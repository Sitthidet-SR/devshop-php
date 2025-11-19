<?php
session_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php';

$page_title = 'จัดการผู้ใช้';

// ลบผู้ใช้
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    if ($user_id != $_SESSION['user_id']) { // ไม่ให้ลบตัวเอง
        $conn->query("DELETE FROM users WHERE user_id = $user_id");
        header('Location: users.php?msg=deleted');
        exit;
    }
}

// เปลี่ยนสถานะ
if (isset($_GET['toggle_status'])) {
    $user_id = intval($_GET['toggle_status']);
    $conn->query("UPDATE users SET status = IF(status = 'active', 'inactive', 'active') WHERE user_id = $user_id");
    header('Location: users.php?msg=updated');
    exit;
}

// ดึงผู้ใช้ทั้งหมด
$all_users = $conn->query("SELECT * FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

// กรองตาม role
$filter = $_GET['role'] ?? 'all';
if ($filter == 'all') {
    $users = $all_users;
} else {
    $users = array_filter($all_users, function($user) use ($filter) {
        return $user['role'] == $filter;
    });
}

// นับจำนวนแต่ละ role
$admin_count = count(array_filter($all_users, function($u) { return $u['role'] == 'admin'; }));
$instructor_count = count(array_filter($all_users, function($u) { return $u['role'] == 'instructor'; }));
$student_count = count(array_filter($all_users, function($u) { return $u['role'] == 'student'; }));
?>

<?php include 'includes/header.php'; ?>

<link rel="stylesheet" href="../assets/css/admin-reviews.css">

<?php if (isset($_GET['msg'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php
            $messages = [
                'deleted' => 'ลบผู้ใช้เรียบร้อยแล้ว',
                'updated' => 'อัพเดทข้อมูลเรียบร้อยแล้ว'
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
            });
            <?php endif; ?>
        });
    </script>
<?php endif; ?>

<div class="content-box">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><i class="fas fa-users"></i> จัดการผู้ใช้</h2>
    </div>

    <div class="filter-tabs" style="margin-bottom: 20px;">
        <a href="users.php" class="filter-tab <?php echo $filter == 'all' ? 'active' : ''; ?>">
            ทั้งหมด <span class="badge"><?php echo count($all_users); ?></span>
        </a>
        <a href="?role=admin" class="filter-tab <?php echo $filter == 'admin' ? 'active' : ''; ?>">
            <i class="fas fa-user-shield"></i> Admin 
            <span class="badge badge-danger"><?php echo $admin_count; ?></span>
        </a>
        <a href="?role=instructor" class="filter-tab <?php echo $filter == 'instructor' ? 'active' : ''; ?>">
            <i class="fas fa-chalkboard-teacher"></i> Instructor 
            <span class="badge badge-primary"><?php echo $instructor_count; ?></span>
        </a>
        <a href="?role=student" class="filter-tab <?php echo $filter == 'student' ? 'active' : ''; ?>">
            <i class="fas fa-user-graduate"></i> Student 
            <span class="badge badge-success"><?php echo $student_count; ?></span>
        </a>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>อีเมล</th>
                    <th>เบอร์โทร</th>
                    <th>บทบาท</th>
                    <th>สถานะ</th>
                    <th>วันที่สมัคร</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['user_id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                        <td>
                            <?php if ($user['role'] == 'admin'): ?>
                                <span class="badge badge-danger">Admin</span>
                            <?php elseif ($user['role'] == 'instructor'): ?>
                                <span class="badge badge-primary">Instructor</span>
                            <?php else: ?>
                                <span class="badge badge-success">Student</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['status'] == 'active'): ?>
                                <span class="badge badge-success">ใช้งาน</span>
                            <?php else: ?>
                                <span class="badge badge-warning">ระงับ</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                        <td style="white-space: nowrap;">
                            <a href="user_edit.php?id=<?php echo $user['user_id']; ?>" class="btn-sm btn-warning">
                                <i class="fas fa-edit"></i> แก้ไข
                            </a>
                            <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                <a href="javascript:void(0)" 
                                   class="btn-sm btn-danger"
                                   onclick="confirmDelete('คุณต้องการลบผู้ใช้นี้หรือไม่?', '?delete=<?php echo $user['user_id']; ?>')">
                                    <i class="fas fa-trash"></i> ลบ
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
