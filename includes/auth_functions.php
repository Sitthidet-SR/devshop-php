<?php
// ฟังก์ชันเกี่ยวกับการ Authentication

// ฟังก์ชันตรวจสอบอีเมลซ้ำ
function email_exists($email)
{
    global $conn;
    $email = $conn->real_escape_string($email);
    
    $sql = "SELECT user_id FROM users WHERE email = '$email'";
    $result = $conn->query($sql);
    
    return $result && $result->num_rows > 0;
}

// ฟังก์ชันสมัครสมาชิก
function register_user($email, $password, $first_name, $last_name, $phone = null)
{
    global $conn;
    
    // ตรวจสอบอีเมลซ้ำ
    if (email_exists($email)) {
        return ['success' => false, 'message' => 'อีเมลนี้ถูกใช้งานแล้ว'];
    }
    
    // เข้ารหัสรหัสผ่าน
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $email = $conn->real_escape_string($email);
    $first_name = $conn->real_escape_string($first_name);
    $last_name = $conn->real_escape_string($last_name);
    $phone = $phone ? $conn->real_escape_string($phone) : null;
    
    $sql = "INSERT INTO users (email, password, first_name, last_name, phone, role, status) 
            VALUES ('$email', '$hashed_password', '$first_name', '$last_name', " . 
            ($phone ? "'$phone'" : "NULL") . ", 'student', 'active')";
    
    if ($conn->query($sql)) {
        return [
            'success' => true, 
            'message' => 'สมัครสมาชิกสำเร็จ',
            'user_id' => $conn->insert_id
        ];
    } else {
        return ['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $conn->error];
    }
}

// ฟังก์ชันเข้าสู่ระบบ
function login_user($email, $password)
{
    global $conn;
    
    $email = $conn->real_escape_string($email);
    
    $sql = "SELECT user_id, email, password, first_name, last_name, role, status 
            FROM users WHERE email = '$email'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // ตรวจสอบสถานะบัญชี
        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => 'บัญชีของคุณถูกระงับ'];
        }
        
        // ตรวจสอบรหัสผ่าน
        if (password_verify($password, $user['password'])) {
            // สร้าง session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_role'] = $user['role'];
            
            return [
                'success' => true, 
                'message' => 'เข้าสู่ระบบสำเร็จ',
                'user' => $user
            ];
        } else {
            return ['success' => false, 'message' => 'รหัสผ่านไม่ถูกต้อง'];
        }
    } else {
        return ['success' => false, 'message' => 'ไม่พบอีเมลนี้ในระบบ'];
    }
}

// ฟังก์ชันออกจากระบบ
function logout_user()
{
    session_unset();
    session_destroy();
    return ['success' => true, 'message' => 'ออกจากระบบสำเร็จ'];
}

// ฟังก์ชันตรวจสอบว่า login หรือไม่
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

// ฟังก์ชันตรวจสอบ role
function check_role($required_role)
{
    if (!is_logged_in()) {
        return false;
    }
    
    $user_role = $_SESSION['user_role'] ?? 'student';
    
    if ($required_role === 'admin') {
        return $user_role === 'admin';
    } elseif ($required_role === 'instructor') {
        return in_array($user_role, ['instructor', 'admin']);
    }
    
    return true;
}
?>
