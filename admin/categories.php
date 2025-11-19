<?php
session_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/category_functions.php';
require_once 'includes/auth_check.php';

$page_title = 'จัดการหมวดหมู่';

$message = '';
$error = '';

// เพิ่มหมวดหมู่
if (isset($_POST['add'])) {
    $name = clean_input($_POST['category_name']);
    $slug = clean_input($_POST['category_slug']);
    $description = clean_input($_POST['description']);
    $icon = clean_input($_POST['icon']);
    
    $sql = "INSERT INTO categories (category_name, category_slug, description, icon, status) 
            VALUES ('$name', '$slug', '$description', '$icon', 'active')";
    
    if ($conn->query($sql)) {
        $message = 'เพิ่มหมวดหมู่เรียบร้อยแล้ว';
    } else {
        $error = 'เกิดข้อผิดพลาด: ' . $conn->error;
    }
}

// แก้ไขหมวดหมู่
if (isset($_POST['edit'])) {
    $id = intval($_POST['category_id']);
    $name = clean_input($_POST['category_name']);
    $slug = clean_input($_POST['category_slug']);
    $description = clean_input($_POST['description']);
    $icon = clean_input($_POST['icon']);
    
    $sql = "UPDATE categories SET 
            category_name = '$name',
            category_slug = '$slug',
            description = '$description',
            icon = '$icon'
            WHERE category_id = $id";
    
    if ($conn->query($sql)) {
        $message = 'แก้ไขหมวดหมู่เรียบร้อยแล้ว';
    } else {
        $error = 'เกิดข้อผิดพลาด: ' . $conn->error;
    }
}

// ลบหมวดหมู่
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // ตรวจสอบว่ามีคอร์สในหมวดหมู่นี้หรือไม่
    $check = $conn->query("SELECT COUNT(*) as count FROM courses WHERE category_id = $id")->fetch_assoc();
    
    if ($check['count'] > 0) {
        $error = 'ไม่สามารถลบได้ เนื่องจากมีคอร์สในหมวดหมู่นี้';
    } else {
        $conn->query("DELETE FROM categories WHERE category_id = $id");
        $message = 'ลบหมวดหมู่เรียบร้อยแล้ว';
    }
}

// ดึงหมวดหมู่ทั้งหมด
$categories = get_all_categories();

// ดึงข้อมูลสำหรับแก้ไข
$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_category = get_category_by_id($edit_id);
}
?>

<?php include 'includes/header.php'; ?>

<link rel="stylesheet" href="../assets/css/admin-reviews.css">

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

<div class="categories-layout">
    <div class="content-box category-form">
        <h2>
            <i class="fas fa-<?php echo $edit_category ? 'edit' : 'plus'; ?>"></i> 
            <?php echo $edit_category ? 'แก้ไข' : 'เพิ่ม'; ?>หมวดหมู่
        </h2>
        <form method="POST">
            <?php if ($edit_category): ?>
                <input type="hidden" name="category_id" value="<?php echo $edit_category['category_id']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label>ชื่อหมวดหมู่ *</label>
                <input type="text" name="category_name" required 
                       value="<?php echo htmlspecialchars($edit_category['category_name'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Slug (URL) *</label>
                <input type="text" name="category_slug" required 
                       value="<?php echo htmlspecialchars($edit_category['category_slug'] ?? ''); ?>">
                <small style="color: #7f8c8d;">ตัวอย่าง: web-development</small>
            </div>

            <div class="form-group">
                <label>คำอธิบาย</label>
                <textarea name="description"><?php echo htmlspecialchars($edit_category['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label>Icon (Font Awesome) *</label>
                <input type="text" name="icon" required 
                       value="<?php echo htmlspecialchars($edit_category['icon'] ?? 'fa-folder'); ?>">
                <small style="color: #7f8c8d;">ตัวอย่าง: fa-laptop-code</small>
            </div>

            <div class="button-group">
                <?php if ($edit_category): ?>
                    <button type="submit" name="edit" class="btn btn-primary">
                        <i class="fas fa-save"></i> บันทึกการแก้ไข
                    </button>
                    <a href="categories.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> ยกเลิก
                    </a>
                <?php else: ?>
                    <button type="submit" name="add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> เพิ่มหมวดหมู่
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="content-box category-list">
        <h2><i class="fas fa-th-large"></i> รายการหมวดหมู่ (<?php echo count($categories); ?>)</h2>
        <table>
            <thead>
                <tr>
                    <th>Icon</th>
                    <th>ชื่อหมวดหมู่</th>
                    <th>Slug</th>
                    <th>จำนวนคอร์ส</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><i class="fas <?php echo htmlspecialchars($cat['icon']); ?>"></i></td>
                        <td><strong><?php echo htmlspecialchars($cat['category_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($cat['category_slug']); ?></td>
                        <td><?php echo $cat['course_count']; ?> คอร์ส</td>
                        <td style="white-space: nowrap;">
                            <a href="?edit=<?php echo $cat['category_id']; ?>" class="btn-sm btn-warning">
                                <i class="fas fa-edit"></i> แก้ไข
                            </a>
                            <a href="javascript:void(0)" 
                               class="btn-sm btn-danger"
                               onclick="confirmDelete('คุณต้องการลบหมวดหมู่นี้หรือไม่?', '?delete=<?php echo $cat['category_id']; ?>')">
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
