<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth_functions.php';

// ตรวจสอบว่า login หรือไม่
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$course_id = intval($_GET['course_id'] ?? 0);

// ตรวจสอบว่าเรียนจบแล้วหรือไม่
$check_sql = "SELECT e.*, c.course_title, c.duration_hours,
              CONCAT(u.first_name, ' ', u.last_name) as student_name,
              CONCAT(i.first_name, ' ', i.last_name) as instructor_name
              FROM enrollments e
              JOIN courses c ON e.course_id = c.course_id
              JOIN users u ON e.user_id = u.user_id
              JOIN users i ON c.instructor_id = i.user_id
              WHERE e.user_id = $user_id AND e.course_id = $course_id AND e.progress = 100";

$result = $conn->query($check_sql);

if (!$result || $result->num_rows == 0) {
    header('Location: my_courses.php');
    exit;
}

$data = $result->fetch_assoc();

// ตรวจสอบว่ามีใบประกาศแล้วหรือไม่
$cert_check = $conn->query("SELECT certificate_code FROM certificates WHERE enrollment_id = {$data['enrollment_id']}");

if ($cert_check && $cert_check->num_rows > 0) {
    // มีใบประกาศแล้ว
    $cert_data = $cert_check->fetch_assoc();
    $certificate_id = $cert_data['certificate_code'];
} else {
    // สร้างใบประกาศใหม่
    $certificate_id = 'CERT-' . date('Y') . '-' . str_pad($data['enrollment_id'], 6, '0', STR_PAD_LEFT);

    // บันทึกลงตาราง certificates
    $conn->query("INSERT INTO certificates (enrollment_id, certificate_code) VALUES ({$data['enrollment_id']}, '$certificate_id')");

    // อัพเดท enrollments
    $conn->query("UPDATE enrollments SET certificate_issued = 1, completed_at = NOW() WHERE enrollment_id = {$data['enrollment_id']}");
}

$issue_date = $data['completed_at'] ? date('d/m/Y', strtotime($data['completed_at'])) : date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบประกาศนียบัตร - <?php echo htmlspecialchars($data['course_title']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Sarabun', 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .certificate-container {
            background: white;
            max-width: 1000px;
            width: 100%;
            padding: 60px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            border: 15px solid #f8f9fa;
        }

        .certificate-border {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            border: 3px solid #667eea;
            border-radius: 10px;
            pointer-events: none;
        }

        .certificate-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }

        .certificate-logo {
            font-size: 60px;
            color: #667eea;
            margin-bottom: 10px;
        }

        .certificate-title {
            font-size: 48px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        .certificate-subtitle {
            font-size: 20px;
            color: #7f8c8d;
            margin-bottom: 30px;
        }

        .certificate-body {
            text-align: center;
            margin: 40px 0;
            position: relative;
            z-index: 1;
        }

        .certificate-text {
            font-size: 18px;
            color: #34495e;
            margin-bottom: 20px;
            line-height: 1.8;
        }

        .student-name {
            font-size: 42px;
            font-weight: 700;
            color: #667eea;
            margin: 30px 0;
            text-decoration: underline;
            text-decoration-color: #764ba2;
            text-decoration-thickness: 3px;
            text-underline-offset: 8px;
        }

        .course-name {
            font-size: 32px;
            font-weight: 600;
            color: #2c3e50;
            margin: 30px 0;
        }

        .certificate-footer {
            display: flex;
            justify-content: space-around;
            margin-top: 60px;
            padding-top: 40px;
            border-top: 2px solid #ecf0f1;
            position: relative;
            z-index: 1;
        }

        .signature-block {
            text-align: center;
        }

        .signature-line {
            width: 250px;
            border-top: 2px solid #2c3e50;
            margin: 0 auto 10px;
            padding-top: 10px;
        }

        .signature-name {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
        }

        .signature-title {
            font-size: 14px;
            color: #7f8c8d;
        }

        .certificate-info {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            position: relative;
            z-index: 1;
        }

        .info-item {
            text-align: center;
        }

        .info-label {
            font-size: 12px;
            color: #7f8c8d;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
        }

        .action-buttons {
            text-align: center;
            margin-top: 30px;
            position: relative;
            z-index: 1;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 0 10px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #667eea;
            color: white;
        }

        @media print {
            body {
                background: white;
            }

            .action-buttons {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .certificate-container {
                padding: 30px;
            }

            .certificate-title {
                font-size: 32px;
            }

            .student-name {
                font-size: 28px;
            }

            .course-name {
                font-size: 22px;
            }
        }
    </style>
</head>

<body>
    <div class="certificate-container">
        <div class="certificate-border"></div>

        <div class="certificate-header">
            <div class="certificate-logo">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h1 class="certificate-title">ใบประกาศนียบัตร</h1>
            <p class="certificate-subtitle">Certificate of Completion</p>
        </div>

        <div class="certificate-body">
            <p class="certificate-text">ขอแสดงความยินดีกับ</p>
            <h2 class="student-name"><?php echo htmlspecialchars($data['student_name']); ?></h2>
            <p class="certificate-text">ได้เรียนจบหลักสูตร</p>
            <h3 class="course-name"><?php echo htmlspecialchars($data['course_title']); ?></h3>
            <p class="certificate-text">จาก DevShop E-Learning Platform</p>
        </div>

        <div class="certificate-footer">
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-name"><?php echo htmlspecialchars($data['instructor_name']); ?></div>
                <div class="signature-title">ผู้สอน</div>
            </div>
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-name">DevShop</div>
                <div class="signature-title">แพลตฟอร์ม</div>
            </div>
        </div>

        <div class="certificate-info">
            <div class="info-item">
                <div class="info-label">เลขที่ใบประกาศ</div>
                <div class="info-value"><?php echo $certificate_id; ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">วันที่ออกใบประกาศ</div>
                <div class="info-value"><?php echo $issue_date; ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">ระยะเวลาเรียน</div>
                <div class="info-value"><?php echo $data['duration_hours']; ?> ชั่วโมง</div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="certificate_pdf.php?id=<?php echo $data['enrollment_id']; ?>" class="btn btn-primary">
                <i class="fas fa-download"></i> ดาวน์โหลด PDF
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> พิมพ์ใบประกาศ
            </button>
            <a href="my_courses.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> กลับไปคอร์สของฉัน
            </a>
        </div>
    </div>
</body>

</html>