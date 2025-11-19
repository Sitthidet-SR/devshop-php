<?php
session_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php';

$page_title = 'Dashboard';

// ดึงสถิติ
$stats = [];

// จำนวนคอร์สทั้งหมด
$result = $conn->query("SELECT COUNT(*) as total FROM courses");
$stats['courses'] = $result->fetch_assoc()['total'];

// จำนวนผู้ใช้ทั้งหมด
$result = $conn->query("SELECT COUNT(*) as total FROM users");
$stats['users'] = $result->fetch_assoc()['total'];

// จำนวนคำสั่งซื้อ
$result = $conn->query("SELECT COUNT(*) as total FROM orders");
$stats['orders'] = $result->fetch_assoc()['total'];

// คำสั่งซื้อรอชำระ
$result = $conn->query("SELECT COUNT(*) as total FROM orders WHERE payment_status = 'pending'");
$stats['pending_orders'] = $result->fetch_assoc()['total'];

// รีวิวรออนุมัติ
$result = $conn->query("SELECT COUNT(*) as total FROM reviews WHERE status = 'pending'");
$stats['pending_reviews'] = $result->fetch_assoc()['total'];

// ข้อความติดต่อใหม่
$result = $conn->query("SELECT COUNT(*) as total FROM contact_messages WHERE status = 'new'");
$stats['new_messages'] = $result->fetch_assoc()['total'];

// รายได้รวม
$result = $conn->query("SELECT SUM(final_amount) as total FROM orders WHERE payment_status = 'completed'");
$stats['revenue'] = $result->fetch_assoc()['total'] ?? 0;

// คอร์สล่าสุด
$recent_courses = $conn->query("SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) as instructor_name 
                                FROM courses c 
                                LEFT JOIN users u ON c.instructor_id = u.user_id 
                                ORDER BY c.created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// ผู้ใช้ล่าสุด
$recent_users = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>

<?php include 'includes/header.php'; ?>

<link rel="stylesheet" href="../assets/css/admin-reviews.css">
<link rel="stylesheet" href="../assets/css/admin-dashboard.css">

<div class="stats-grid">
    <div class="stat-card blue">
        <i class="fas fa-book"></i>
        <h3>คอร์สทั้งหมด</h3>
        <div class="number"><?php echo number_format($stats['courses']); ?></div>
    </div>
    <div class="stat-card green">
        <i class="fas fa-users"></i>
        <h3>ผู้ใช้ทั้งหมด</h3>
        <div class="number"><?php echo number_format($stats['users']); ?></div>
    </div>
    <a href="orders.php" class="stat-card orange" style="text-decoration: none; display: block;">
        <i class="fas fa-shopping-cart"></i>
        <h3>คำสั่งซื้อ</h3>
        <div class="number"><?php echo number_format($stats['orders']); ?></div>
    </a>
    <div class="stat-card purple">
        <i class="fas fa-dollar-sign"></i>
        <h3>รายได้รวม</h3>
        <div class="number">฿<?php echo number_format($stats['revenue'], 2); ?></div>
    </div>
</div>

<div style="margin-bottom: 30px;">
    <h2 style="margin-bottom: 15px;"><i class="fas fa-cog"></i> เมนูจัดการ</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        <a href="courses.php" style="background: white; padding: 20px; border-radius: 8px; text-decoration: none; color: #2c3e50; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <i class="fas fa-book" style="font-size: 32px; color: #3498db; margin-bottom: 10px;"></i>
            <div style="font-weight: 600;">จัดการคอร์ส</div>
        </a>
        <a href="orders.php" style="background: white; padding: 20px; border-radius: 8px; text-decoration: none; color: #2c3e50; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <i class="fas fa-shopping-cart" style="font-size: 32px; color: #e67e22; margin-bottom: 10px;"></i>
            <div style="font-weight: 600;">จัดการคำสั่งซื้อ</div>
        </a>
        <a href="contact_messages.php" style="background: white; padding: 20px; border-radius: 8px; text-decoration: none; color: #2c3e50; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <i class="fas fa-envelope" style="font-size: 32px; color: #9b59b6; margin-bottom: 10px;"></i>
            <div style="font-weight: 600;">ข้อความติดต่อ</div>
        </a>
        <a href="users.php" style="background: white; padding: 20px; border-radius: 8px; text-decoration: none; color: #2c3e50; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <i class="fas fa-users" style="font-size: 32px; color: #2ecc71; margin-bottom: 10px;"></i>
            <div style="font-weight: 600;">จัดการผู้ใช้</div>
        </a>
    </div>
</div>

<div class="content-grid">
    <div class="content-box">
        <h2><i class="fas fa-book"></i> คอร์สล่าสุด</h2>
        <?php foreach ($recent_courses as $course): ?>
            <div class="list-item">
                <strong><?php echo htmlspecialchars($course['course_title']); ?></strong><br>
                <small>โดย <?php echo htmlspecialchars($course['instructor_name']); ?> |
                    <?php echo date('d/m/Y', strtotime($course['created_at'])); ?></small>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="content-box">
        <h2><i class="fas fa-users"></i> ผู้ใช้ล่าสุด</h2>
        <?php foreach ($recent_users as $user): ?>
            <div class="list-item">
                <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong><br>
                <small><?php echo htmlspecialchars($user['email']); ?> |
                    <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></small>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>