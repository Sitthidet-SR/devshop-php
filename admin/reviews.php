<?php
session_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php';

$page_title = 'จัดการรีวิว';

// อัพเดทสถานะรีวิว
if (isset($_GET['approve'])) {
    $review_id = intval($_GET['approve']);
    $conn->query("UPDATE reviews SET status = 'approved' WHERE review_id = $review_id");
    header('Location: reviews.php?msg=approved');
    exit;
}

if (isset($_GET['reject'])) {
    $review_id = intval($_GET['reject']);
    $conn->query("UPDATE reviews SET status = 'rejected' WHERE review_id = $review_id");
    header('Location: reviews.php?msg=rejected');
    exit;
}

if (isset($_GET['delete'])) {
    $review_id = intval($_GET['delete']);
    $conn->query("DELETE FROM reviews WHERE review_id = $review_id");
    header('Location: reviews.php?msg=deleted');
    exit;
}

$filter = $_GET['filter'] ?? 'all';

$where = '';
if ($filter == 'pending') {
    $where = "WHERE r.status = 'pending'";
} elseif ($filter == 'approved') {
    $where = "WHERE r.status = 'approved'";
} elseif ($filter == 'rejected') {
    $where = "WHERE r.status = 'rejected'";
}

$reviews = $conn->query("
    SELECT r.*, c.course_title, CONCAT(u.first_name, ' ', u.last_name) as user_name
    FROM reviews r
    LEFT JOIN courses c ON r.course_id = c.course_id
    LEFT JOIN users u ON r.user_id = u.user_id
    $where
    ORDER BY r.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$pending_count = $conn->query("SELECT COUNT(*) as count FROM reviews WHERE status = 'pending'")->fetch_assoc()['count'];
$approved_count = $conn->query("SELECT COUNT(*) as count FROM reviews WHERE status = 'approved'")->fetch_assoc()['count'];
$rejected_count = $conn->query("SELECT COUNT(*) as count FROM reviews WHERE status = 'rejected'")->fetch_assoc()['count'];
$total_count = $conn->query("SELECT COUNT(*) as count FROM reviews")->fetch_assoc()['count'];
?>

<?php include 'includes/header.php'; ?>

<link rel="stylesheet" href="../assets/css/admin-reviews.css">

<?php if (isset($_GET['msg'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php
            $messages = [
                'approved' => 'อนุมัติรีวิวเรียบร้อยแล้ว',
                'rejected' => 'ปฏิเสธรีวิวเรียบร้อยแล้ว',
                'deleted' => 'ลบรีวิวเรียบร้อยแล้ว'
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
        <h2><i class="fas fa-star"></i> รายการรีวิว</h2>
    </div>

    <div class="filter-tabs" style="margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="reviews.php?filter=all" class="filter-tab <?php echo $filter == 'all' ? 'active' : ''; ?>">
            ทั้งหมด <span class="badge"><?php echo $total_count; ?></span>
        </a>
        <a href="reviews.php?filter=pending" class="filter-tab <?php echo $filter == 'pending' ? 'active' : ''; ?>">
            รออนุมัติ <span class="badge badge-warning"><?php echo $pending_count; ?></span>
        </a>
        <a href="reviews.php?filter=approved" class="filter-tab <?php echo $filter == 'approved' ? 'active' : ''; ?>">
            อนุมัติแล้ว <span class="badge badge-success"><?php echo $approved_count; ?></span>
        </a>
        <a href="reviews.php?filter=rejected" class="filter-tab <?php echo $filter == 'rejected' ? 'active' : ''; ?>">
            ปฏิเสธ <span class="badge badge-danger"><?php echo $rejected_count; ?></span>
        </a>
    </div>

    <?php if (empty($reviews)): ?>
        <p style="text-align: center; padding: 40px; color: #7f8c8d;">ยังไม่มีรีวิว</p>
    <?php else: ?>
        <?php foreach ($reviews as $review): ?>
            <div class="review-item">
                <div class="review-header">
                    <div class="review-info">
                        <strong><?php echo htmlspecialchars($review['user_name']); ?></strong>
                        <span class="rating">
                            <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                <i class="fas fa-star"></i>
                            <?php endfor; ?>
                            <?php for ($i = $review['rating']; $i < 5; $i++): ?>
                                <i class="far fa-star"></i>
                            <?php endfor; ?>
                        </span>
                        <br>
                        <small>คอร์ส: <?php echo htmlspecialchars($review['course_title']); ?></small><br>
                        <small><?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?></small>
                    </div>
                    <div>
                        <?php if ($review['status'] == 'approved'): ?>
                            <span class="badge badge-success">อนุมัติแล้ว</span>
                        <?php elseif ($review['status'] == 'pending'): ?>
                            <span class="badge badge-warning">รออนุมัติ</span>
                        <?php else: ?>
                            <span class="badge badge-danger">ปฏิเสธ</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($review['comment']): ?>
                    <div class="review-comment">
                        "<?php echo htmlspecialchars($review['comment']); ?>"
                    </div>
                <?php endif; ?>

                <div class="review-actions">
                    <?php if ($review['status'] != 'approved'): ?>
                        <a href="javascript:void(0)" 
                           class="btn-sm btn-success"
                           onclick="confirmAction('อนุมัติรีวิว', 'คุณต้องการอนุมัติรีวิวนี้หรือไม่?', '?approve=<?php echo $review['review_id']; ?>')">
                            <i class="fas fa-check"></i> อนุมัติ
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($review['status'] != 'rejected'): ?>
                        <a href="javascript:void(0)" 
                           class="btn-sm btn-warning"
                           onclick="confirmAction('ปฏิเสธรีวิว', 'คุณต้องการปฏิเสธรีวิวนี้หรือไม่?', '?reject=<?php echo $review['review_id']; ?>')">
                            <i class="fas fa-times"></i> ปฏิเสธ
                        </a>
                    <?php endif; ?>
                    
                    <a href="javascript:void(0)" 
                       class="btn-sm btn-danger"
                       onclick="confirmDelete('คุณต้องการลบรีวิวนี้หรือไม่?', '?delete=<?php echo $review['review_id']; ?>')">
                        <i class="fas fa-trash"></i> ลบ
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
