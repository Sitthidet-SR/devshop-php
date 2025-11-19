<?php
// ดึงชื่อผู้ใช้จากฐานข้อมูลเพื่อให้ได้ข้อมูลล่าสุด
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_result = $conn->query("SELECT first_name, last_name FROM users WHERE user_id = $user_id");
    if ($user_result && $user_result->num_rows > 0) {
        $user_data = $user_result->fetch_assoc();
        $_SESSION['user_name'] = $user_data['first_name'] . ' ' . $user_data['last_name'];
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Panel'; ?> - DevShop Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/sweetalert-helper.js"></script>
    <script>
        // Handle URL parameters for alerts
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const msg = urlParams.get('msg');

            const messages = {
                'added': {
                    type: 'success',
                    text: 'เพิ่มข้อมูลสำเร็จ!'
                },
                'updated': {
                    type: 'success',
                    text: 'อัพเดทข้อมูลสำเร็จ!'
                },
                'deleted': {
                    type: 'success',
                    text: 'ลบข้อมูลสำเร็จ!'
                },
                'approved': {
                    type: 'success',
                    text: 'อนุมัติสำเร็จ!'
                },
                'rejected': {
                    type: 'success',
                    text: 'ปฏิเสธสำเร็จ!'
                },
                'error': {
                    type: 'error',
                    text: 'เกิดข้อผิดพลาด!'
                }
            };

            if (msg && messages[msg]) {
                Swal.fire({
                    icon: messages[msg].type,
                    title: messages[msg].type === 'success' ? 'สำเร็จ!' : 'เกิดข้อผิดพลาด!',
                    text: messages[msg].text,
                    confirmButtonColor: '#667eea',
                    confirmButtonText: 'ตกลง',
                    timer: 2000,
                    timerProgressBar: true
                });

                // Remove msg parameter from URL
                urlParams.delete('msg');
                const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
                window.history.replaceState({}, '', newUrl);
            }
        });
    </script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 20px;
            background: #1a252f;
            text-align: center;
        }

        .sidebar-header h2 {
            font-size: 20px;
            margin-bottom: 5px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .sidebar-menu li a:hover {
            background: #34495e;
            border-left-color: #667eea;
            padding-left: 25px;
        }

        .sidebar-menu li a.active {
            background: #34495e;
            border-left-color: #667eea;
            color: white;
        }

        .sidebar-menu li a i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        .sidebar-menu li a span {
            margin-left: auto;
        }

        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
            width: calc(100% - 250px);
            min-height: 100vh;
        }

        .top-bar {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .top-bar h1 {
            font-size: 24px;
            color: #2c3e50;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .btn {
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #764ba2;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: #2c3e50;
            color: white;
            border: none;
            padding: 12px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 20px;
        }

        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
                z-index: 1000;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .top-bar {
                margin-left: 60px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 250px;
            }

            .top-bar h1 {
                font-size: 18px;
            }

            .user-info span {
                display: none;
            }
        }
    </style>
</head>

<body>
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="admin-container">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-code"></i> DevShop</h2>
                <p>Admin Panel</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a></li>

                <!-- จัดการเนื้อหา -->
                <li style="padding: 10px 20px; color: #95a5a6; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-top: 10px;">
                    <i class="fas fa-folder"></i> จัดการเนื้อหา
                </li>
                <li><a href="courses.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'courses.php' ? 'active' : ''; ?>">
                        <i class="fas fa-book"></i> คอร์ส
                    </a></li>
                <li><a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
                        <i class="fas fa-th-large"></i> หมวดหมู่
                    </a></li>

                <!-- จัดการผู้ใช้ -->
                <li style="padding: 10px 20px; color: #95a5a6; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-top: 10px;">
                    <i class="fas fa-users-cog"></i> จัดการผู้ใช้
                </li>
                <li><a href="users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> ผู้ใช้ทั้งหมด
                    </a></li>

                <!-- จัดการคำสั่งซื้อ -->
                <li style="padding: 10px 20px; color: #95a5a6; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-top: 10px;">
                    <i class="fas fa-shopping-bag"></i> ยอดขาย
                </li>
                <li><a href="orders.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
                        <i class="fas fa-shopping-cart"></i> คำสั่งซื้อ
                        <?php
                        $new_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE payment_status = 'pending'")->fetch_assoc()['count'];
                        if ($new_orders > 0):
                        ?>
                            <span style="background: #28a745; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; margin-left: auto; font-weight: 600;"><?php echo $new_orders; ?></span>
                        <?php endif; ?>
                    </a></li>

                <!-- จัดการรีวิวและข้อความ -->
                <li style="padding: 10px 20px; color: #95a5a6; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-top: 10px;">
                    <i class="fas fa-comments"></i> การสื่อสาร
                </li>
                <li><a href="reviews.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active' : ''; ?>">
                        <i class="fas fa-star"></i> รีวิว
                        <?php
                        $new_reviews = $conn->query("SELECT COUNT(*) as count FROM reviews WHERE status = 'pending'")->fetch_assoc()['count'];
                        if ($new_reviews > 0):
                        ?>
                            <span style="background: #ffc107; color: #212529; padding: 2px 8px; border-radius: 10px; font-size: 11px; margin-left: auto; font-weight: 600;"><?php echo $new_reviews; ?></span>
                        <?php endif; ?>
                    </a></li>
                <li><a href="contact_messages.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contact_messages.php' ? 'active' : ''; ?>">
                        <i class="fas fa-envelope"></i> ข้อความติดต่อ
                        <?php
                        // แสดงจำนวนข้อความใหม่
                        $new_messages = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'new'")->fetch_assoc()['count'];
                        if ($new_messages > 0):
                        ?>
                            <span style="background: #e74c3c; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; margin-left: auto;"><?php echo $new_messages; ?></span>
                        <?php endif; ?>
                    </a></li>

                <!-- อื่นๆ -->
                <li style="padding: 10px 20px; color: #95a5a6; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-top: 10px;">
                    <i class="fas fa-cog"></i> อื่นๆ
                </li>
                <li><a href="../index.php">
                        <i class="fas fa-home"></i> ดูหน้าเว็บ
                    </a></li>
                <li><a href="../logout.php">
                        <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
                    </a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <h1><?php echo $page_title ?? 'Dashboard'; ?></h1>
                <div class="user-info">
                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                </div>
            </div>