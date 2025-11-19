# คู่มือติดตั้ง DevShop

## ความต้องการของระบบ
- PHP 7.4 หรือสูงกว่า
- MySQL 5.7 หรือสูงกว่า
- Apache/Nginx Web Server
- Extensions: mysqli, gd, mbstring

## ขั้นตอนการติดตั้ง

### 1. คัดลอกไฟล์โปรเจค
```bash
# คัดลอกโฟลเดอร์ devshop ไปยัง document root
cp -r devshop /var/www/html/
```

### 2. สร้างฐานข้อมูล
```bash
# เข้าสู่ MySQL
mysql -u root -p

# สร้างฐานข้อมูล
CREATE DATABASE devshop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Import ข้อมูล
mysql -u root -p devshop < sql/devshop_with_data.sql
```

### 3. ตั้งค่าการเชื่อมต่อฐานข้อมูล
แก้ไขไฟล์ `includes/config.php`:
```php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'your_password';
$db_name = 'devshop';
```

### 4. ตั้งค่า PHP สำหรับอัพโหลดไฟล์ขนาดใหญ่

#### วิธีที่ 1: แก้ไข php.ini (แนะนำ)
```bash
# หาไฟล์ php.ini
php --ini

# แก้ไขไฟล์ (สำหรับ Apache)
sudo nano /etc/php/8.3/apache2/php.ini
```

เพิ่ม/แก้ไขค่าเหล่านี้:
```ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
memory_limit = 256M
max_input_time = 300
```

Restart Apache:
```bash
sudo systemctl restart apache2
```

#### วิธีที่ 2: ใช้ .htaccess (มีอยู่แล้วในโปรเจค)
ไฟล์ `.htaccess` ได้ตั้งค่าไว้แล้ว แต่อาจไม่ทำงานในบาง hosting

### 5. ตั้งค่า Permissions
```bash
# ให้สิทธิ์เขียนไฟล์
chmod -R 755 /var/www/html/devshop
chmod -R 777 /var/www/html/devshop/uploads
```

### 6. เข้าใช้งานระบบ
เปิดเบราว์เซอร์และไปที่:
```
http://localhost/devshop/
```

## บัญชีทดสอบ

### Admin
- Email: admin@devshop.com
- Password: 123456

### นักเรียน
- Email: user1@test.com
- Password: 123456

- Email: user2@test.com
- Password: 123456

## การแก้ปัญหา

### ปัญหา: อัพโหลดไฟล์ไม่ได้
1. ตรวจสอบค่า `upload_max_filesize` และ `post_max_size` ใน php.ini
2. ตรวจสอบ permissions ของโฟลเดอร์ uploads
3. ดูข้อผิดพลาดใน error log: `/var/log/apache2/error.log`

### ปัญหา: เชื่อมต่อฐานข้อมูลไม่ได้
1. ตรวจสอบ username/password ใน `includes/config.php`
2. ตรวจสอบว่า MySQL service ทำงานอยู่: `sudo systemctl status mysql`
3. ตรวจสอบว่าสร้างฐานข้อมูลแล้ว: `SHOW DATABASES;`

### ปัญหา: หน้าเว็บแสดงไม่ถูกต้อง
1. ตรวจสอบ Apache rewrite module: `sudo a2enmod rewrite`
2. Restart Apache: `sudo systemctl restart apache2`
3. ตรวจสอบ .htaccess ว่าอนุญาตให้ใช้งาน

## ข้อมูลเพิ่มเติม

### โครงสร้างโฟลเดอร์
```
devshop/
├── admin/          # หน้าจัดการระบบ
├── assets/         # CSS, JS, Images
├── includes/       # ไฟล์ PHP ที่ใช้ร่วมกัน
├── sql/           # ไฟล์ SQL
├── uploads/       # ไฟล์ที่อัพโหลด
└── *.php          # หน้าเว็บหลัก
```

### ฐานข้อมูล
- users - ข้อมูลผู้ใช้
- courses - คอร์สเรียน
- categories - หมวดหมู่
- sections - หัวข้อในคอร์ส
- lectures - บทเรียน
- enrollments - การลงทะเบียนเรียน
- reviews - รีวิวคอร์ส
- orders - คำสั่งซื้อ
- cart - ตะกร้าสินค้า
- wishlist - รายการถูกใจ

## ติดต่อ
- Email: Sitthidet.SR@gmail.com
- Facebook: https://www.facebook.com/SitthidetSR/
- GitHub: https://github.com/Sitthidet-SR
