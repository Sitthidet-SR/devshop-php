<?php
session_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php';

$page_title = 'ข้อความติดต่อ';

// อัพเดทสถานะ
if (isset($_GET['mark_read'])) {
    $message_id = intval($_GET['mark_read']);
    $conn->query("UPDATE contact_messages SET status = 'read' WHERE message_id = $message_id");
    header('Location: contact_messages.php');
    exit;
}

if (isset($_GET['mark_replied'])) {
    $message_id = intval($_GET['mark_replied']);
    $conn->query("UPDATE contact_messages SET status = 'replied', replied_at = NOW() WHERE message_id = $message_id");
    header('Location: contact_messages.php');
    exit;
}

if (isset($_GET['delete'])) {
    $message_id = intval($_GET['delete']);
    $conn->query("DELETE FROM contact_messages WHERE message_id = $message_id");
    header('Location: contact_messages.php');
    exit;
}

// ดึงข้อความทั้งหมด
$filter = isset($_GET['status']) ? clean_input($_GET['status']) : 'all';

$sql = "SELECT * FROM contact_messages WHERE 1=1";
if ($filter != 'all') {
    $sql .= " AND status = '$filter'";
}
$sql .= " ORDER BY created_at DESC";

$messages = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// สถิติ
$stats = [
    'all' => $conn->query("SELECT COUNT(*) as count FROM contact_messages")->fetch_assoc()['count'],
    'new' => $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'new'")->fetch_assoc()['count'],
    'read' => $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'read'")->fetch_assoc()['count'],
    'replied' => $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'replied'")->fetch_assoc()['count']
];
?>

<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="../assets/css/admin-reviews.css">

<div class="content-box">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><i class="fas fa-envelope"></i> ข้อความติดต่อ</h2>
    </div>

    <div class="filter-tabs" style="margin-bottom: 20px;">
        <a href="contact_messages.php" class="filter-tab <?php echo $filter == 'all' ? 'active' : ''; ?>">
            ทั้งหมด <span class="badge"><?php echo $stats['all']; ?></span>
        </a>
        <a href="?status=new" class="filter-tab <?php echo $filter == 'new' ? 'active' : ''; ?>">
            <i class="fas fa-star"></i> ใหม่ <span class="badge badge-warning"><?php echo $stats['new']; ?></span>
        </a>
        <a href="?status=read" class="filter-tab <?php echo $filter == 'read' ? 'active' : ''; ?>">
            <i class="fas fa-eye"></i> อ่านแล้ว <span class="badge"><?php echo $stats['read']; ?></span>
        </a>
        <a href="?status=replied" class="filter-tab <?php echo $filter == 'replied' ? 'active' : ''; ?>">
            <i class="fas fa-check-circle"></i> ตอบกลับแล้ว <span class="badge badge-success"><?php echo $stats['replied']; ?></span>
        </a>
    </div>

    <!-- Messages List -->
    <div class="messages-list">
            <?php if (empty($messages)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h2>ไม่มีข้อความ</h2>
                    <p>ยังไม่มีข้อความติดต่อในขณะนี้</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message-item <?php echo $msg['status'] == 'new' ? 'unread' : ''; ?>">
                        <div class="message-header">
                            <div class="message-info">
                                <h3><?php echo htmlspecialchars($msg['subject']); ?></h3>
                                <div class="message-meta">
                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($msg['name']); ?></span>
                                    <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($msg['email']); ?></span>
                                    <span><i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?></span>
                                </div>
                            </div>
                            <span class="status-badge status-<?php echo $msg['status']; ?>">
                                <?php 
                                $status_text = ['new' => 'ใหม่', 'read' => 'อ่านแล้ว', 'replied' => 'ตอบกลับแล้ว'];
                                echo $status_text[$msg['status']];
                                ?>
                            </span>
                        </div>
                        <div class="message-content">
                            <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                        </div>
                        <div class="message-actions">
                            <?php if ($msg['status'] == 'new'): ?>
                                <a href="?mark_read=<?php echo $msg['message_id']; ?>&status=<?php echo $filter; ?>" class="btn-sm btn-success">
                                    <i class="fas fa-check"></i> อ่านแล้ว
                                </a>
                            <?php endif; ?>
                            <?php if ($msg['status'] != 'replied'): ?>
                                <a href="javascript:void(0)" 
                                   class="btn-sm btn-success"
                                   onclick="replyViaGmail('<?php echo htmlspecialchars($msg['email']); ?>', '<?php echo htmlspecialchars(addslashes($msg['subject'])); ?>', <?php echo $msg['message_id']; ?>, '<?php echo $filter; ?>')">
                                    <i class="fas fa-reply"></i> ตอบกลับ
                                </a>
                            <?php endif; ?>
                            <a href="javascript:void(0)" 
                               class="btn-sm btn-danger"
                               onclick="confirmDelete('คุณต้องการลบข้อความนี้หรือไม่?', '?delete=<?php echo $msg['message_id']; ?>&status=<?php echo $filter; ?>')">
                                <i class="fas fa-trash"></i> ลบ
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
    </div>
</div>

    <script>
        function replyViaGmail(email, subject, messageId, filter) {
            // สร้าง Gmail compose URL
            const gmailUrl = `https://mail.google.com/mail/?view=cm&fs=1&to=${encodeURIComponent(email)}&su=${encodeURIComponent('Re: ' + subject)}`;
            
            // เปิด Gmail ในแท็บใหม่
            window.open(gmailUrl, '_blank');
            
            // แสดง confirmation dialog
            Swal.fire({
                title: 'ทำเครื่องหมายว่าตอบกลับแล้ว?',
                text: 'คุณต้องการทำเครื่องหมายข้อความนี้ว่าตอบกลับแล้วหรือไม่?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ใช่, ทำเครื่องหมาย',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `?mark_replied=${messageId}&status=${filter}`;
                }
            });
        }
    </script>

<?php include 'includes/footer.php'; ?>
