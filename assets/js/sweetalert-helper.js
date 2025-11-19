// ฟังก์ชันยืนยันการลบ
function confirmDelete(message = 'คุณต้องการลบรายการนี้หรือไม่?', url = null) {
    Swal.fire({
        title: 'ยืนยันการลบ',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#95a5a6',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed && url) {
            window.location.href = url;
        }
    });
    return false;
}

// ฟังก์ชันยืนยันทั่วไป
function confirmAction(title, message, url = null) {
    Swal.fire({
        title: title,
        text: message,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#667eea',
        cancelButtonColor: '#95a5a6',
        confirmButtonText: 'ยืนยัน',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed && url) {
            window.location.href = url;
        }
    });
    return false;
}

// ฟังก์ชันแสดงข้อความสำเร็จ
function showSuccess(message, title = 'สำเร็จ!') {
    Swal.fire({
        icon: 'success',
        title: title,
        text: message,
        confirmButtonColor: '#667eea',
        confirmButtonText: 'ตกลง',
        timer: 2000,
        timerProgressBar: true
    });
}

// ฟังก์ชันแสดงข้อความผิดพลาด
function showError(message, title = 'เกิดข้อผิดพลาด!') {
    Swal.fire({
        icon: 'error',
        title: title,
        text: message,
        confirmButtonColor: '#667eea',
        confirmButtonText: 'ตกลง'
    });
}

// ฟังก์ชันแสดงข้อความแจ้งเตือน
function showWarning(message, title = 'คำเตือน!') {
    Swal.fire({
        icon: 'warning',
        title: title,
        text: message,
        confirmButtonColor: '#667eea',
        confirmButtonText: 'ตกลง'
    });
}

// ฟังก์ชันแสดงข้อความทั่วไป
function showInfo(message, title = 'ข้อมูล') {
    Swal.fire({
        icon: 'info',
        title: title,
        text: message,
        confirmButtonColor: '#667eea',
        confirmButtonText: 'ตกลง'
    });
}

// ฟังก์ชันแสดงข้อความสำเร็จพร้อม redirect
function showSuccessAndRedirect(message, url, title = 'สำเร็จ!', timer = 2000) {
    Swal.fire({
        icon: 'success',
        title: title,
        text: message,
        confirmButtonColor: '#667eea',
        confirmButtonText: 'ตกลง',
        timer: timer,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        window.location.href = url;
    });
}

// ฟังก์ชันสำหรับแสดง alert จาก PHP
function showAlertFromPHP(type, message, title = null) {
    const icons = {
        'success': 'success',
        'error': 'error',
        'warning': 'warning',
        'info': 'info'
    };

    const titles = {
        'success': title || 'สำเร็จ!',
        'error': title || 'เกิดข้อผิดพลาด!',
        'warning': title || 'คำเตือน!',
        'info': title || 'ข้อมูล'
    };

    Swal.fire({
        icon: icons[type] || 'info',
        title: titles[type],
        text: message,
        confirmButtonColor: '#667eea',
        confirmButtonText: 'ตกลง',
        timer: type === 'success' ? 2000 : null,
        timerProgressBar: type === 'success'
    });
}

// Auto-show alerts from PHP on page load
document.addEventListener('DOMContentLoaded', function () {
    // Check for PHP alert data
    const alertData = document.getElementById('php-alert-data');
    if (alertData) {
        const type = alertData.dataset.type;
        const message = alertData.dataset.message;
        const title = alertData.dataset.title;
        if (type && message) {
            showAlertFromPHP(type, message, title);
        }
    }

    // Check for URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');

    if (msg) {
        showMessageFromURL(msg);

        // Remove msg parameter from URL
        urlParams.delete('msg');
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.replaceState({}, '', newUrl);
    }
});

// Show message from URL parameter
function showMessageFromURL(msg) {
    const messages = {
        // Auth messages
        'logout': { icon: 'success', title: 'ออกจากระบบแล้ว', text: 'ขอบคุณที่ใช้บริการ' },
        'login': { icon: 'success', title: 'เข้าสู่ระบบสำเร็จ', text: 'ยินดีต้อนรับกลับมา!' },

        // Cart messages
        'added_to_cart': { icon: 'success', title: 'เพิ่มลงตะกร้าแล้ว', text: 'เพิ่มคอร์สลงตะกร้าเรียบร้อยแล้ว' },
        'already_in_cart': { icon: 'info', title: 'มีในตะกร้าแล้ว', text: 'คอร์สนี้มีในตะกร้าของคุณอยู่แล้ว' },
        'removed_from_cart': { icon: 'success', title: 'ลบออกจากตะกร้าแล้ว', text: 'ลบคอร์สออกจากตะกร้าเรียบร้อยแล้ว' },

        // Wishlist messages
        'added_to_wishlist': { icon: 'success', title: 'เพิ่มลงรายการถูกใจแล้ว', text: 'เพิ่มคอร์สลงรายการถูกใจเรียบร้อยแล้ว' },
        'already_in_wishlist': { icon: 'info', title: 'มีในรายการถูกใจแล้ว', text: 'คอร์สนี้มีในรายการถูกใจของคุณอยู่แล้ว' },
        'removed_from_wishlist': { icon: 'success', title: 'ลบออกจากรายการถูกใจแล้ว', text: 'ลบคอร์สออกจากรายการถูกใจเรียบร้อยแล้ว' },

        // Order messages
        'order_success': { icon: 'success', title: 'สั่งซื้อสำเร็จ!', text: 'ขอบคุณสำหรับการสั่งซื้อ คุณสามารถเข้าเรียนได้ทันที' },
        'payment_success': { icon: 'success', title: 'ชำระเงินสำเร็จ!', text: 'การชำระเงินเสร็จสมบูรณ์' },

        // Profile messages
        'profile_updated': { icon: 'success', title: 'อัพเดทโปรไฟล์สำเร็จ', text: 'ข้อมูลของคุณได้รับการอัพเดทแล้ว' },
        'password_changed': { icon: 'success', title: 'เปลี่ยนรหัสผ่านสำเร็จ', text: 'รหัสผ่านของคุณได้รับการเปลี่ยนแล้ว' },
        'avatar_uploaded': { icon: 'success', title: 'อัพโหลดรูปสำเร็จ', text: 'รูปโปรไฟล์ของคุณได้รับการอัพเดทแล้ว' },
        'avatar_deleted': { icon: 'success', title: 'ลบรูปสำเร็จ', text: 'รูปโปรไฟล์ของคุณถูกลบแล้ว' },

        // Review messages
        'review_submitted': { icon: 'success', title: 'ส่งรีวิวสำเร็จ', text: 'รีวิวของคุณรอการอนุมัติ' },
        'review_updated': { icon: 'success', title: 'อัพเดทรีวิวสำเร็จ', text: 'รีวิวของคุณได้รับการอัพเดทแล้ว' },

        // Contact messages
        'message_sent': { icon: 'success', title: 'ส่งข้อความสำเร็จ', text: 'เราจะติดต่อกลับโดยเร็วที่สุด' },

        // Error messages
        'error': { icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'กรุณาลองใหม่อีกครั้ง' },
        'unauthorized': { icon: 'error', title: 'ไม่มีสิทธิ์เข้าถึง', text: 'กรุณาเข้าสู่ระบบก่อน' }
    };

    // ถ้าไม่เจอ message code ให้แสดงข้อความทั่วไป
    let config = messages[msg];
    
    if (!config) {
        // ถ้า msg เป็น URL encoded ให้ decode
        const decodedMsg = decodeURIComponent(msg);
        config = { 
            icon: 'success', 
            title: 'สำเร็จ!', 
            text: decodedMsg 
        };
    }

    Swal.fire({
        icon: config.icon,
        title: config.title,
        text: config.text,
        confirmButtonColor: '#667eea',
        confirmButtonText: 'ตกลง',
        timer: config.icon === 'success' || config.icon === 'info' ? 2500 : null,
        timerProgressBar: config.icon === 'success' || config.icon === 'info'
    });
}
