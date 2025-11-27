<?php
session_start();
require_once 'includes/config.php';
require_once 'vendor/TCPDF-main/tcpdf.php';

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$enrollment_id = $_GET['id'] ?? 0;

// ตรวจสอบว่าเรียนจบและเป็นของผู้ใช้คนนี้
$check_sql = "SELECT e.*, co.course_title, co.duration_hours,
              CONCAT(u.first_name, ' ', u.last_name) as student_name,
              CONCAT(i.first_name, ' ', i.last_name) as instructor_name
              FROM enrollments e
              JOIN courses co ON e.course_id = co.course_id
              JOIN users u ON e.user_id = u.user_id
              JOIN users i ON co.instructor_id = i.user_id
              WHERE e.enrollment_id = ? AND e.user_id = ? AND e.progress = 100";

$stmt = $conn->prepare($check_sql);
$stmt->bind_param('ii', $enrollment_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('ไม่พบใบประกาศนี้ หรือคุณยังเรียนไม่จบ');
}

$enrollment_data = $result->fetch_assoc();

// ตรวจสอบว่ามีใบประกาศแล้วหรือไม่
$cert_check = $conn->query("SELECT certificate_code, issued_at FROM certificates WHERE enrollment_id = $enrollment_id");

if ($cert_check && $cert_check->num_rows > 0) {
    $cert_data = $cert_check->fetch_assoc();
    $certificate_code = $cert_data['certificate_code'];
    $issued_at = $cert_data['issued_at'];
} else {
    $certificate_code = 'CERT-' . date('Y') . '-' . str_pad($enrollment_id, 6, '0', STR_PAD_LEFT);
    $conn->query("INSERT INTO certificates (enrollment_id, certificate_code) VALUES ($enrollment_id, '$certificate_code')");
    $conn->query("UPDATE enrollments SET certificate_issued = 1, completed_at = NOW() WHERE enrollment_id = $enrollment_id");
    $issued_at = date('Y-m-d H:i:s');
}

// สร้าง PDF ด้วย TCPDF
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

// ตั้งค่า PDF
$pdf->SetCreator('DevShop');
$pdf->SetAuthor('DevShop E-Learning');
$pdf->SetTitle('Certificate of Completion');
$pdf->SetSubject('Course Certificate');

// ลบ header/footer default
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// ตั้งค่า margins
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(false, 0);

// เพิ่มหน้า
$pdf->AddPage();

// พื้นหลัง
$pdf->SetFillColor(245, 247, 250);
$pdf->Rect(0, 0, 297, 210, 'F');

// กรอบหลัก
$pdf->SetLineWidth(2);
$pdf->SetDrawColor(102, 126, 234);
$pdf->Rect(10, 10, 277, 190);

$pdf->SetLineWidth(0.5);
$pdf->SetDrawColor(118, 75, 162);
$pdf->Rect(15, 15, 267, 180);

// ใช้ฟอนต์ที่รองรับภาษาไทย (freeserif รองรับ UTF-8)
$pdf->SetFont('freeserif', 'B', 40);
$pdf->SetTextColor(102, 126, 234);
$pdf->SetXY(15, 30);
$pdf->Cell(267, 20, 'ใบประกาศนียบัตร', 0, 1, 'C', 0, '', 0);

// ข้อความ "Certificate of Achievement"
$pdf->SetFont('helvetica', 'I', 16);
$pdf->SetTextColor(100, 100, 100);
$pdf->SetXY(15, 52);
$pdf->Cell(267, 10, 'Certificate of Achievement', 0, 1, 'C', 0, '', 0);

// เส้นแบ่ง
$pdf->SetDrawColor(200, 200, 200);
$pdf->Line(80, 68, 217, 68);

// ข้อความ "ขอแสดงความยินดีกับ"
$pdf->SetFont('freeserif', '', 14);
$pdf->SetTextColor(80, 80, 80);
$pdf->SetXY(15, 75);
$pdf->Cell(267, 8, 'ขอแสดงความยินดีกับ', 0, 1, 'C', 0, '', 0);

// ชื่อผู้เรียน
$pdf->SetFont('freeserif', 'B', 28);
$pdf->SetTextColor(44, 62, 80);
$pdf->SetXY(15, 88);
$pdf->Cell(267, 12, $enrollment_data['student_name'], 0, 1, 'C', 0, '', 0);

// เส้นใต้ชื่อ
$pdf->SetDrawColor(102, 126, 234);
$pdf->Line(90, 102, 207, 102);

// ข้อความ "ได้เรียนจบหลักสูตร"
$pdf->SetFont('freeserif', '', 14);
$pdf->SetTextColor(80, 80, 80);
$pdf->SetXY(15, 108);
$pdf->Cell(267, 8, 'ได้เรียนจบหลักสูตร', 0, 1, 'C', 0, '', 0);

// ชื่อคอร์ส
$pdf->SetFont('freeserif', 'B', 18);
$pdf->SetTextColor(102, 126, 234);
$pdf->SetXY(15, 120);
$pdf->MultiCell(267, 8, $enrollment_data['course_title'], 0, 'C', 0, 1, '', '', true);

// วันที่
$pdf->SetFont('freeserif', '', 12);
$pdf->SetTextColor(100, 100, 100);
$pdf->SetXY(15, 145);
$thai_months = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 
                'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
$date_parts = explode('-', date('d-n-Y', strtotime($issued_at)));
$issued_date = $date_parts[0] . ' ' . $thai_months[(int)$date_parts[1]] . ' ' . ($date_parts[2] + 543);
$pdf->Cell(267, 8, 'ออกให้เมื่อวันที่ ' . $issued_date, 0, 1, 'C', 0, '', 0);

// ส่วนล่าง - ผู้สอนและรหัสใบประกาศ
$pdf->SetY(165);

// ผู้สอน (ซ้าย)
$pdf->SetFont('freeserif', 'B', 14);
$pdf->SetTextColor(44, 62, 80);
$pdf->SetX(50);
$pdf->Cell(80, 8, $enrollment_data['instructor_name'], 0, 0, 'C', 0, '', 0);

// รหัสใบประกาศ (ขวา)
$pdf->SetX(167);
$pdf->Cell(80, 8, $certificate_code, 0, 1, 'C', 0, '', 0);

// เส้นใต้ลายเซ็น
$pdf->SetDrawColor(150, 150, 150);
$pdf->Line(50, 173, 130, 173);
$pdf->Line(167, 173, 247, 173);

// ข้อความใต้เส้น
$pdf->SetFont('freeserif', '', 11);
$pdf->SetTextColor(120, 120, 120);
$pdf->SetY(175);
$pdf->SetX(50);
$pdf->Cell(80, 6, 'ผู้สอน', 0, 0, 'C', 0, '', 0);
$pdf->SetX(167);
$pdf->Cell(80, 6, 'รหัสใบประกาศ', 0, 1, 'C', 0, '', 0);

// ข้อความท้าย
$pdf->SetFont('helvetica', 'I', 8);
$pdf->SetTextColor(150, 150, 150);
$pdf->SetXY(15, 183);
$pdf->Cell(267, 4, 'DevShop - Online Learning Platform', 0, 1, 'C', 0, '', 0);
$pdf->SetXY(15, 187);
$pdf->Cell(267, 4, 'Verify: ' . $_SERVER['HTTP_HOST'] . '/devshop/verify_certificate.php?code=' . $certificate_code, 0, 1, 'C', 0, '', 0);

// ส่งออก PDF
$filename = 'Certificate_' . $certificate_code . '.pdf';
$pdf->Output($filename, 'D');
