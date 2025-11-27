# คู่มือการติดตั้ง DevShop

คู่มือนี้จะแนะนำการติดตั้ง DevShop บน Windows, macOS และ Linux

## 📋 ความต้องการของระบบ

- PHP 7.4 หรือสูงกว่า
- MySQL 5.7 หรือสูงกว่า (หรือ MariaDB 10.2+)
- Apache/Nginx Web Server
- PHP Extensions: mysqli, gd, mbstring, curl, zip

---

## 🪟 Windows

### วิธีที่ 1: ใช้ XAMPP (แนะนำสำหรับมือใหม่)

#### 1. ติดตั้ง XAMPP

1. ดาวน์โหลด XAMPP จาก https://www.apachefriends.org/
2. ติดตั้ง XAMPP (แนะนำติดตั้งที่ `C:\xampp`)
3. เปิด XAMPP Control Panel
4. Start Apache และ MySQL

#### 2. คัดลอกไฟล์โปรเจค

```cmd
# คัดลอกโฟลเดอร์ devshop ไปยัง
C:\xampp\htdocs\devshop
```

#### 3. สร้างฐานข้อมูล

1. เปิดเบราว์เซอร์ไปที่ http://localhost/phpmyadmin
2. คลิก "New" เพื่อสร้างฐานข้อมูลใหม่
3. ตั้งชื่อ `devshop`
4. Collation: `utf8mb4_unicode_ci`
5. คลิก "Import" แล้วเลือกไฟล์ `sql/devshop.sql`

#### 4. ตั้งค่าการเชื่อมต่อฐานข้อมูล

แก้ไขไฟล์ `C:\xampp\htdocs\devshop\includes\config.php`:

```php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';  // XAMPP ไม่มีรหัสผ่าน default
$db_name = 'devshop';
```

#### 5. ตั้งค่า PHP สำหรับอัพโหลดไฟล์ขนาดใหญ่

แก้ไขไฟล์ `C:\xampp\php\php.ini`:

```ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
memory_limit = 256M
```

รีสตาร์ท Apache จาก XAMPP Control Panel

#### 6. เปิดเว็บไซต์

เปิดเบราว์เซอร์ไปที่: http://localhost/devshop

### วิธีที่ 2: ใช้ Laragon

#### 1. ติดตั้ง Laragon

1. ดาวน์โหลด Laragon จาก https://laragon.org/download/
2. ติดตั้ง Laragon
3. เปิด Laragon และ Start All

#### 2. คัดลอกโปรเจค

```cmd
# คัดลอกโฟลเดอร์ devshop ไปยัง
C:\laragon\www\devshop
```

#### 3. สร้างฐานข้อมูล

1. คลิกขวาที่ Laragon > MySQL > Create Database
2. ตั้งชื่อ `devshop`
3. เปิด HeidiSQL (มากับ Laragon)
4. Import ไฟล์ `sql/devshop.sql`

#### 4. ตั้งค่าการเชื่อมต่อ

แก้ไขไฟล์ `includes\config.php`:

```php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'devshop';
```

#### 5. เปิดเว็บไซต์

http://devshop.test (Laragon จะสร้าง virtual host อัตโนมัติ)

---

## 🍎 macOS

### วิธีที่ 1: ใช้ MAMP

#### 1. ติดตั้ง MAMP

```bash
# ดาวน์โหลดจาก https://www.mamp.info/
# หรือใช้ Homebrew
brew install --cask mamp
```

#### 2. คัดลอกโปรเจค

```bash
cp -r devshop /Applications/MAMP/htdocs/
```

#### 3. สร้างฐานข้อมูล

1. เปิด MAMP และ Start Servers
2. เปิด http://localhost:8888/phpMyAdmin
3. สร้างฐานข้อมูล `devshop`
4. Import ไฟล์ `sql/devshop.sql`

#### 4. ตั้งค่าการเชื่อมต่อ

```bash
nano /Applications/MAMP/htdocs/devshop/includes/config.php
```

```php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'root';  // MAMP default password
$db_name = 'devshop';
```

#### 5. เปิดเว็บไซต์

http://localhost:8888/devshop

### วิธีที่ 2: ใช้ Homebrew (สำหรับ Developer)

#### 1. ติดตั้ง PHP และ MySQL

```bash
# ติดตั้ง Homebrew (ถ้ายังไม่มี)
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# ติดตั้ง PHP
brew install php@8.1

# ติดตั้ง MySQL
brew install mysql

# Start MySQL
brew services start mysql
```

#### 2. ติดตั้ง Apache (มากับ macOS แล้ว)

```bash
# Start Apache
sudo apachectl start

# เปิด PHP module
sudo nano /etc/apache2/httpd.conf
# ค้นหาและเอา # ออกจากบรรทัดนี้:
# LoadModule php_module libexec/apache2/libphp.so
```

#### 3. คัดลอกโปรเจค

```bash
sudo cp -r devshop /Library/WebServer/Documents/
sudo chmod -R 755 /Library/WebServer/Documents/devshop
sudo chmod -R 777 /Library/WebServer/Documents/devshop/uploads
```

#### 4. สร้างฐานข้อมูล

```bash
mysql -u root -p
```

```sql
CREATE DATABASE devshop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

```bash
mysql -u root -p devshop < sql/devshop.sql
```

#### 5. ตั้งค่าการเชื่อมต่อ

```bash
nano includes/config.php
```

#### 6. Restart Apache

```bash
sudo apachectl restart
```

#### 7. เปิดเว็บไซต์

http://localhost/devshop

---

## 🐧 Linux (Ubuntu/Debian)

### ติดตั้งแบบ LAMP Stack

#### 1. อัพเดทระบบ

```bash
sudo apt update
sudo apt upgrade -y
```

#### 2. ติดตั้ง Apache

```bash
sudo apt install apache2 -y
sudo systemctl start apache2
sudo systemctl enable apache2
```

#### 3. ติดตั้ง MySQL

```bash
sudo apt install mysql-server -y
sudo mysql_secure_installation
```

#### 4. ติดตั้ง PHP

```bash
sudo apt install php libapache2-mod-php php-mysql php-gd php-mbstring php-curl php-zip -y
```

#### 5. เปิด mod_rewrite

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### 6. คัดลอกโปรเจค

```bash
sudo cp -r devshop /var/www/html/
sudo chown -R www-data:www-data /var/www/html/devshop
sudo chmod -R 755 /var/www/html/devshop
sudo chmod -R 777 /var/www/html/devshop/uploads
```

#### 7. สร้างฐานข้อมูล

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE devshop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'devshop_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON devshop.* TO 'devshop_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

```bash
mysql -u devshop_user -p devshop < sql/devshop.sql
```

#### 8. ตั้งค่าการเชื่อมต่อ

```bash
sudo nano /var/www/html/devshop/includes/config.php
```

```php
$db_host = 'localhost';
$db_user = 'devshop_user';
$db_pass = 'your_password';
$db_name = 'devshop';
```

#### 9. ตั้งค่า Virtual Host (Optional)

```bash
sudo nano /etc/apache2/sites-available/devshop.conf
```

```apache
<VirtualHost *:80>
    ServerName devshop.local
    DocumentRoot /var/www/html/devshop

    <Directory /var/www/html/devshop>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/devshop_error.log
    CustomLog ${APACHE_LOG_DIR}/devshop_access.log combined
</VirtualHost>
```

```bash
sudo a2ensite devshop.conf
sudo systemctl reload apache2

# เพิ่มใน /etc/hosts
echo "127.0.0.1 devshop.local" | sudo tee -a /etc/hosts
```

#### 10. เปิดเว็บไซต์

http://localhost/devshop หรือ http://devshop.local

---

## 📦 การติดตั้ง TCPDF (สำหรับใบประกาศ PDF)

### ทุก OS

```bash
cd /path/to/devshop
mkdir -p vendor
wget https://github.com/tecnickcom/TCPDF/archive/refs/heads/main.zip -O vendor/tcpdf.zip
unzip vendor/tcpdf.zip -d vendor/
```

หรือใช้ไฟล์ที่มีอยู่แล้วใน `vendor/TCPDF-main/`

---

## 🔧 การตั้งค่าเพิ่มเติม

### ตั้งค่า PHP สำหรับอัพโหลดไฟล์ขนาดใหญ่

#### Windows (XAMPP)

แก้ไข `C:\xampp\php\php.ini`

#### macOS (MAMP)

แก้ไข `/Applications/MAMP/bin/php/php8.x.x/conf/php.ini`

#### Linux

แก้ไข `/etc/php/8.1/apache2/php.ini`

```ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
memory_limit = 256M
max_input_time = 300
```

รีสตาร์ทเว็บเซิร์ฟเวอร์หลังแก้ไข

---

## ✅ ทดสอบการติดตั้ง

1. เปิดเว็บไซต์ http://localhost/devshop
2. ล็อกอินด้วยบัญชีแอดมิน:
   - Email: `admin@devshop.com`
   - Password: `admin123`
3. ทดสอบฟีเจอร์ต่างๆ:
   - ดูคอร์ส
   - เพิ่มคอร์สในตะกร้า
   - ชำระเงิน
   - เรียนคอร์ส
   - ดาวน์โหลดใบประกาศ

---

## 🐛 แก้ปัญหาที่พบบ่อย

### ปัญหา: ไม่สามารถเชื่อมต่อฐานข้อมูล

- ตรวจสอบ username/password ใน `includes/config.php`
- ตรวจสอบว่า MySQL service ทำงานอยู่
- ตรวจสอบว่าสร้างฐานข้อมูล `devshop` แล้ว

### ปัญหา: อัพโหลดไฟล์ไม่ได้

- ตรวจสอบ `upload_max_filesize` ใน php.ini
- ตรวจสอบ permissions ของโฟลเดอร์ `uploads/`
- รีสตาร์ทเว็บเซิร์ฟเวอร์

### ปัญหา: URL Rewriting ไม่ทำงาน

- ตรวจสอบว่าเปิด `mod_rewrite` แล้ว
- ตรวจสอบไฟล์ `.htaccess` มีอยู่
- ตรวจสอบ `AllowOverride All` ใน Apache config

### ปัญหา: ใบประกาศ PDF ภาษาไทยเพี้ยน

- ตรวจสอบว่าติดตั้ง TCPDF แล้ว
- ตรวจสอบว่าใช้ฟอนต์ `freeserif` ใน certificate_pdf.php

---

## 📞 ติดต่อสอบถาม

หากพบปัญหาในการติดตั้ง:

- เปิด Issue บน [GitHub](https://github.com/Sitthidet-SR/devshop-php/issues)
- ติดต่อผ่านหน้า Contact Us บนเว็บไซต์
- ดูเอกสารเพิ่มเติมที่ [README.md](README.md)

---
