mysqldump: [Warning] Using a password on the command line interface can be insecure.
-- MySQL dump 10.13  Distrib 8.0.43, for Linux (x86_64)
--
-- Host: localhost    Database: devshop
-- ------------------------------------------------------
-- Server version	8.0.43-0ubuntu0.24.04.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart` (
  `cart_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `course_id` int NOT NULL,
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cart_id`),
  UNIQUE KEY `unique_cart` (`user_id`,`course_id`),
  KEY `course_id` (`course_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart`
--

LOCK TABLES `cart` WRITE;
/*!40000 ALTER TABLE `cart` DISABLE KEYS */;
/*!40000 ALTER TABLE `cart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_order` int DEFAULT '0',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_slug` (`category_slug`),
  KEY `idx_slug` (`category_slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Web Development','web-development','เรียนรู้การพัฒนาเว็บไซต์ HTML, CSS, JavaScript และ Framework ต่างๆ','fa-code',0,'active','2025-11-19 14:20:07'),(2,'Mobile Development','mobile-development','พัฒนาแอปพลิเคชันมือถือสำหรับ iOS และ Android','fa-mobile-alt',0,'active','2025-11-19 14:20:07'),(3,'Database','database','เรียนรู้การจัดการฐานข้อมูล MySQL, MongoDB, PostgreSQL','fa-database',0,'active','2025-11-19 14:20:07'),(4,'Programming','programming','เรียนรู้ภาษาโปรแกรมมิ่ง Python, Java, C++, PHP','fa-laptop-code',0,'active','2025-11-19 14:20:07'),(5,'Data Science','data-science','วิเคราะห์ข้อมูล Machine Learning และ AI','fa-chart-line',0,'active','2025-11-19 14:20:07'),(6,'DevOps','devops','เรียนรู้ Docker, Kubernetes, CI/CD และ Cloud Computing','fa-server',0,'active','2025-11-19 14:20:07');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `certificates`
--

DROP TABLE IF EXISTS `certificates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `certificates` (
  `certificate_id` int NOT NULL AUTO_INCREMENT,
  `enrollment_id` int NOT NULL,
  `certificate_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `issued_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `certificate_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`certificate_id`),
  UNIQUE KEY `certificate_number` (`certificate_number`),
  KEY `idx_enrollment` (`enrollment_id`),
  KEY `idx_number` (`certificate_number`),
  CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`enrollment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `certificates`
--

LOCK TABLES `certificates` WRITE;
/*!40000 ALTER TABLE `certificates` DISABLE KEYS */;
/*!40000 ALTER TABLE `certificates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_messages` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('new','read','replied') COLLATE utf8mb4_unicode_ci DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `replied_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
INSERT INTO `contact_messages` VALUES (1,'สมชาย ใจดี','somchai@example.com','สอบถามเกี่ยวกับคอร์ส React','สวัสดีครับ อยากสอบถามว่าคอร์ส React.js มีเนื้อหาเกี่ยวกับ Redux ด้วยหรือไม่ครับ','new','2025-11-19 14:31:25',NULL),(2,'สมหญิง รักเรียน','somying@example.com','ขอใบเสร็จ','สวัสดีค่ะ อยากขอใบเสร็จสำหรับคอร์สที่ซื้อไปค่ะ','new','2025-11-19 14:31:25',NULL),(3,'ประยุทธ์ โค้ดดี','prayut@example.com','สนใจเป็นผู้สอน','สวัสดีครับ ผมสนใจเป็นผู้สอนในแพลตฟอร์มครับ มีประสบการณ์สอน Python มา 5 ปี','new','2025-11-19 14:31:25',NULL),(4,'วิชัย พัฒนา','wichai@example.com','แจ้งปัญหาการเข้าสู่ระบบ','สวัสดีครับ ผมเข้าสู่ระบบไม่ได้ครับ ช่วยตรวจสอบให้หน่อยครับ','new','2025-11-19 14:31:25',NULL),(5,'นภา สวยงาม','napa@example.com','สอบถามราคาคอร์ส','สวัสดีค่ะ อยากทราบว่ามีส่วนลดสำหรับซื้อหลายคอร์สพร้อมกันไหมคะ','new','2025-11-19 14:31:25',NULL);
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupon_usage`
--

DROP TABLE IF EXISTS `coupon_usage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupon_usage` (
  `usage_id` int NOT NULL AUTO_INCREMENT,
  `coupon_id` int NOT NULL,
  `user_id` int NOT NULL,
  `order_id` int NOT NULL,
  `used_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`usage_id`),
  KEY `order_id` (`order_id`),
  KEY `idx_coupon` (`coupon_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `coupon_usage_ibfk_1` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`coupon_id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_usage_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_usage_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupon_usage`
--

LOCK TABLES `coupon_usage` WRITE;
/*!40000 ALTER TABLE `coupon_usage` DISABLE KEYS */;
/*!40000 ALTER TABLE `coupon_usage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupons` (
  `coupon_id` int NOT NULL AUTO_INCREMENT,
  `coupon_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_type` enum('percentage','fixed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_purchase` decimal(10,2) DEFAULT '0.00',
  `max_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int DEFAULT NULL,
  `used_count` int DEFAULT '0',
  `valid_from` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `valid_until` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive','expired') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`coupon_id`),
  UNIQUE KEY `coupon_code` (`coupon_code`),
  KEY `idx_code` (`coupon_code`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `courses` (
  `course_id` int NOT NULL AUTO_INCREMENT,
  `instructor_id` int NOT NULL,
  `category_id` int NOT NULL,
  `course_title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `course_slug` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `short_description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `level` enum('beginner','intermediate','advanced') COLLATE utf8mb4_unicode_ci DEFAULT 'beginner',
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `duration_hours` decimal(5,2) DEFAULT NULL,
  `total_lectures` int DEFAULT '0',
  `status` enum('draft','published','archived') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `featured` tinyint(1) DEFAULT '0',
  `bestseller` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`course_id`),
  UNIQUE KEY `course_slug` (`course_slug`),
  KEY `idx_slug` (`course_slug`),
  KEY `idx_instructor` (`instructor_id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_status` (`status`),
  KEY `idx_featured` (`featured`),
  CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `courses_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (1,1,1,'HTML &amp; CSS สำหรับผู้เริ่มต้น','html-css-beginner','คอร์สนี้จะสอนพื้นฐาน HTML และ CSS ตั้งแต่เริ่มต้น เหมาะสำหรับผู้ที่ไม่มีพื้นฐานการเขียนโค้ดมาก่อน','เรียนรู้พื้นฐาน HTML และ CSS เพื่อสร้างเว็บไซต์','https://via.placeholder.com/400x250/667eea/ffffff?text=HTML+CSS','beginner',1990.00,990.00,15.00,0,'published',1,1,'2025-11-19 14:24:02','2025-11-19 14:24:50'),(2,1,1,'JavaScript เบื้องต้น','javascript-beginner','เรียนรู้ภาษา JavaScript ที่เป็นหัวใจของการพัฒนาเว็บไซต์สมัยใหม่','เรียนรู้ JavaScript จากพื้นฐานถึงขั้นสูง','https://via.placeholder.com/400x250/f39c12/ffffff?text=JavaScript','beginner',2490.00,1490.00,20.00,0,'published',0,1,'2025-11-19 14:24:02','2025-11-19 14:24:41'),(3,1,1,'React.js สำหรับมือใหม่','reactjs-beginner','เรียนรู้การสร้าง Web Application ด้วย React.js Framework ที่ได้รับความนิยมสูงสุด','สร้างเว็บแอปพลิเคชันด้วย React','https://via.placeholder.com/400x250/61dafb/ffffff?text=React.js','intermediate',3990.00,2490.00,25.00,0,'published',1,0,'2025-11-19 14:24:02','2025-11-19 14:24:02'),(4,1,1,'Node.js & Express Backend','nodejs-express-backend','เรียนรู้การสร้าง Backend API ด้วย Node.js และ Express Framework','พัฒนา RESTful API แบบมืออาชีพ','https://via.placeholder.com/400x250/68a063/ffffff?text=Node.js','intermediate',3490.00,2190.00,23.00,0,'published',1,0,'2025-11-19 14:24:02','2025-11-19 14:24:02'),(5,1,1,'PHP &amp; MySQL เบื้องต้น','php-mysql-beginner','เรียนรู้การพัฒนาเว็บไซต์ด้วย PHP และการจัดการฐานข้อมูล MySQL','สร้างเว็บไซต์แบบ Dynamic ด้วย PHP','https://via.placeholder.com/400x250/8892be/ffffff?text=PHP+MySQL','beginner',2990.00,1790.00,18.00,0,'published',0,1,'2025-11-19 14:24:02','2025-11-19 14:25:08'),(6,1,4,'Python สำหรับผู้เริ่มต้น','python-beginner','เริ่มต้นเขียนโปรแกรมด้วย Python ภาษาที่ได้รับความนิยมสูงสุด','เรียนรู้ Python จากศูนย์','https://via.placeholder.com/400x250/3776ab/ffffff?text=Python','beginner',2490.00,1490.00,16.00,0,'published',1,0,'2025-11-19 14:24:02','2025-11-19 14:24:02'),(7,1,3,'MySQL Database Design','mysql-database-design','เรียนรู้การออกแบบและจัดการฐานข้อมูล MySQL แบบมืออาชีพ','ออกแบบฐานข้อมูลอย่างมืออาชีพ','https://via.placeholder.com/400x250/00758f/ffffff?text=MySQL','intermediate',2790.00,1590.00,14.00,0,'published',0,0,'2025-11-19 14:24:02','2025-11-19 14:24:02'),(8,1,2,'Flutter Mobile App Development','flutter-mobile-app','พัฒนาแอปพลิเคชันมือถือสำหรับ iOS และ Android ด้วย Flutter','สร้างแอปมือถือด้วย Flutter','https://via.placeholder.com/400x250/02569b/ffffff?text=Flutter','intermediate',4490.00,2990.00,30.00,0,'published',1,0,'2025-11-19 14:24:02','2025-11-19 14:24:02');
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enrollments`
--

DROP TABLE IF EXISTS `enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `enrollments` (
  `enrollment_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `course_id` int NOT NULL,
  `order_id` int DEFAULT NULL,
  `enrolled_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `progress` int DEFAULT '0',
  `completed_at` timestamp NULL DEFAULT NULL,
  `certificate_issued` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`enrollment_id`),
  UNIQUE KEY `unique_enrollment` (`user_id`,`course_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_course` (`course_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  CONSTRAINT `enrollments_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enrollments`
--

LOCK TABLES `enrollments` WRITE;
/*!40000 ALTER TABLE `enrollments` DISABLE KEYS */;
INSERT INTO `enrollments` VALUES (1,2,1,NULL,'2025-11-19 14:30:30',100,NULL,0),(2,2,2,NULL,'2025-11-19 14:30:30',75,NULL,0),(3,2,3,NULL,'2025-11-19 14:30:30',50,NULL,0),(4,3,1,NULL,'2025-11-19 14:30:30',100,NULL,0),(5,3,4,NULL,'2025-11-19 14:30:30',80,NULL,0),(6,3,6,NULL,'2025-11-19 14:30:30',60,NULL,0);
/*!40000 ALTER TABLE `enrollments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lecture_progress`
--

DROP TABLE IF EXISTS `lecture_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lecture_progress` (
  `progress_id` int NOT NULL AUTO_INCREMENT,
  `enrollment_id` int NOT NULL,
  `lecture_id` int NOT NULL,
  `completed` tinyint(1) DEFAULT '0',
  `completed_at` timestamp NULL DEFAULT NULL,
  `last_position` int DEFAULT '0',
  PRIMARY KEY (`progress_id`),
  UNIQUE KEY `unique_progress` (`enrollment_id`,`lecture_id`),
  KEY `lecture_id` (`lecture_id`),
  KEY `idx_enrollment` (`enrollment_id`),
  CONSTRAINT `lecture_progress_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`enrollment_id`) ON DELETE CASCADE,
  CONSTRAINT `lecture_progress_ibfk_2` FOREIGN KEY (`lecture_id`) REFERENCES `lectures` (`lecture_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lecture_progress`
--

LOCK TABLES `lecture_progress` WRITE;
/*!40000 ALTER TABLE `lecture_progress` DISABLE KEYS */;
/*!40000 ALTER TABLE `lecture_progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lectures`
--

DROP TABLE IF EXISTS `lectures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lectures` (
  `lecture_id` int NOT NULL AUTO_INCREMENT,
  `section_id` int NOT NULL,
  `lecture_title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lecture_type` enum('video','article','quiz','file') COLLATE utf8mb4_unicode_ci DEFAULT 'video',
  `content_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_text` text COLLATE utf8mb4_unicode_ci,
  `duration_minutes` int DEFAULT NULL,
  `lecture_order` int DEFAULT '0',
  `is_preview` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`lecture_id`),
  KEY `idx_section` (`section_id`),
  CONSTRAINT `lectures_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lectures`
--

LOCK TABLES `lectures` WRITE;
/*!40000 ALTER TABLE `lectures` DISABLE KEYS */;
INSERT INTO `lectures` VALUES (1,1,'แนะนำคอร์สและเครื่องมือ','video','https://www.youtube.com/watch?v=example1',NULL,10,1,1,'2025-11-19 14:28:16'),(2,1,'HTML Tags พื้นฐาน','video','https://www.youtube.com/watch?v=example2',NULL,15,2,0,'2025-11-19 14:28:16'),(3,1,'HTML Forms','video','https://www.youtube.com/watch?v=example3',NULL,20,3,0,'2025-11-19 14:28:16'),(4,2,'CSS Selectors','video','https://www.youtube.com/watch?v=example4',NULL,18,1,0,'2025-11-19 14:28:16'),(5,2,'Colors และ Backgrounds','video','https://www.youtube.com/watch?v=example5',NULL,15,2,0,'2025-11-19 14:28:16'),(6,2,'Flexbox Layout','video','https://www.youtube.com/watch?v=example6',NULL,25,3,0,'2025-11-19 14:28:16'),(7,3,'Media Queries','video','https://www.youtube.com/watch?v=example7',NULL,20,1,0,'2025-11-19 14:28:16'),(8,3,'Mobile First Design','video','https://www.youtube.com/watch?v=example8',NULL,22,2,0,'2025-11-19 14:28:16'),(9,4,'ตัวแปรและ Data Types','video','https://www.youtube.com/watch?v=example9',NULL,15,1,0,'2025-11-19 14:28:16'),(10,4,'Functions','video','https://www.youtube.com/watch?v=example10',NULL,20,2,0,'2025-11-19 14:28:16'),(11,4,'Arrays และ Objects','video','https://www.youtube.com/watch?v=example11',NULL,25,3,0,'2025-11-19 14:28:16'),(12,5,'เลือก Elements','video','https://www.youtube.com/watch?v=example12',NULL,18,1,0,'2025-11-19 14:28:16'),(13,5,'Event Listeners','video','https://www.youtube.com/watch?v=example13',NULL,22,2,0,'2025-11-19 14:28:16'),(14,6,'Promises','video','https://www.youtube.com/watch?v=example14',NULL,20,1,0,'2025-11-19 14:28:16'),(15,6,'Async/Await','video','https://www.youtube.com/watch?v=example15',NULL,25,2,0,'2025-11-19 14:28:16'),(16,7,'ติดตั้ง React','video','https://www.youtube.com/watch?v=example16',NULL,12,1,0,'2025-11-19 14:28:16'),(17,7,'JSX Syntax','video','https://www.youtube.com/watch?v=example17',NULL,18,2,0,'2025-11-19 14:28:16'),(18,8,'สร้าง Components','video','https://www.youtube.com/watch?v=example18',NULL,20,1,0,'2025-11-19 14:28:16'),(19,8,'Props และ Children','video','https://www.youtube.com/watch?v=example19',NULL,22,2,0,'2025-11-19 14:28:16'),(20,9,'useState Hook','video','https://www.youtube.com/watch?v=example20',NULL,25,1,0,'2025-11-19 14:28:16'),(21,9,'useEffect Hook','video','https://www.youtube.com/watch?v=example21',NULL,28,2,0,'2025-11-19 14:28:16'),(22,10,'ติดตั้ง Node.js','video','https://www.youtube.com/watch?v=ex1',NULL,10,1,0,'2025-11-19 14:28:16'),(23,10,'NPM และ Modules','video','https://www.youtube.com/watch?v=ex2',NULL,15,2,0,'2025-11-19 14:28:16'),(24,11,'สร้าง Express Server','video','https://www.youtube.com/watch?v=ex3',NULL,20,1,0,'2025-11-19 14:28:16'),(25,11,'Routing และ Middleware','video','https://www.youtube.com/watch?v=ex4',NULL,25,2,0,'2025-11-19 14:28:16'),(26,12,'PHP Syntax','video','https://www.youtube.com/watch?v=ex5',NULL,15,1,0,'2025-11-19 14:28:16'),(27,12,'Variables และ Functions','video','https://www.youtube.com/watch?v=ex6',NULL,20,2,0,'2025-11-19 14:28:16'),(28,13,'เชื่อมต่อ MySQL','video','https://www.youtube.com/watch?v=ex7',NULL,18,1,0,'2025-11-19 14:28:16'),(29,13,'CRUD Operations','video','https://www.youtube.com/watch?v=ex8',NULL,25,2,0,'2025-11-19 14:28:16'),(30,14,'Python Basics','video','https://www.youtube.com/watch?v=ex9',NULL,15,1,0,'2025-11-19 14:28:16'),(31,14,'Data Types','video','https://www.youtube.com/watch?v=ex10',NULL,20,2,0,'2025-11-19 14:28:16'),(32,15,'OOP in Python','video','https://www.youtube.com/watch?v=ex11',NULL,25,1,0,'2025-11-19 14:28:16'),(33,15,'File Handling','video','https://www.youtube.com/watch?v=ex12',NULL,20,2,0,'2025-11-19 14:28:16'),(34,16,'Database Normalization','video','https://www.youtube.com/watch?v=ex13',NULL,22,1,0,'2025-11-19 14:28:16'),(35,16,'ER Diagram','video','https://www.youtube.com/watch?v=ex14',NULL,18,2,0,'2025-11-19 14:28:16'),(36,17,'SELECT Queries','video','https://www.youtube.com/watch?v=ex15',NULL,20,1,0,'2025-11-19 14:28:16'),(37,17,'JOIN Operations','video','https://www.youtube.com/watch?v=ex16',NULL,25,2,0,'2025-11-19 14:28:16'),(38,18,'Flutter Installation','video','https://www.youtube.com/watch?v=ex17',NULL,15,1,0,'2025-11-19 14:28:16'),(39,18,'Dart Language','video','https://www.youtube.com/watch?v=ex18',NULL,20,2,0,'2025-11-19 14:28:16'),(40,19,'Stateless Widgets','video','https://www.youtube.com/watch?v=ex19',NULL,22,1,0,'2025-11-19 14:28:16'),(41,19,'Stateful Widgets','video','https://www.youtube.com/watch?v=ex20',NULL,25,2,0,'2025-11-19 14:28:16'),(42,18,'Flutter Installation','video','https://www.youtube.com/watch?v=ex17',NULL,15,1,0,'2025-11-19 14:28:38'),(43,18,'Dart Language','video','https://www.youtube.com/watch?v=ex18',NULL,20,2,0,'2025-11-19 14:28:38'),(44,19,'Stateless Widgets','video','https://www.youtube.com/watch?v=ex19',NULL,22,1,0,'2025-11-19 14:28:38'),(45,19,'Stateful Widgets','video','https://www.youtube.com/watch?v=ex20',NULL,25,2,0,'2025-11-19 14:28:38'),(46,21,'Flutter Installation','video','https://www.youtube.com/watch?v=flutter1',NULL,15,1,0,'2025-11-19 14:29:31'),(47,21,'Dart Language Basics','video','https://www.youtube.com/watch?v=flutter2',NULL,20,2,0,'2025-11-19 14:29:31'),(48,21,'First Flutter App','video','https://www.youtube.com/watch?v=flutter3',NULL,25,3,0,'2025-11-19 14:29:31'),(49,22,'Stateless Widgets','video','https://www.youtube.com/watch?v=flutter4',NULL,22,1,0,'2025-11-19 14:29:31'),(50,22,'Stateful Widgets','video','https://www.youtube.com/watch?v=flutter5',NULL,25,2,0,'2025-11-19 14:29:31'),(51,22,'Layout Widgets','video','https://www.youtube.com/watch?v=flutter6',NULL,28,3,0,'2025-11-19 14:29:31');
/*!40000 ALTER TABLE `lectures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('info','success','warning','error') COLLATE utf8mb4_unicode_ci DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT '0',
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_read` (`is_read`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `order_item_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `course_id` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`order_item_id`),
  KEY `course_id` (`course_id`),
  KEY `idx_order` (`order_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `order_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT '0.00',
  `final_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('credit_card','paypal','bank_transfer','promptpay') COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_status` enum('pending','completed','failed','refunded') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `billing_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_address` text COLLATE utf8mb4_unicode_ci,
  `paid_at` timestamp NULL DEFAULT NULL,
  `transaction_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_status` enum('pending','processing','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `idx_user` (`user_id`),
  KEY `idx_order_number` (`order_number`),
  KEY `idx_payment_status` (`payment_status`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `review_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `course_id` int NOT NULL,
  `rating` int NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`),
  UNIQUE KEY `unique_review` (`user_id`,`course_id`),
  KEY `idx_course` (`course_id`),
  KEY `idx_rating` (`rating`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_chk_1` CHECK (((`rating` >= 1) and (`rating` <= 5)))
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,2,1,5,'คอร์สดีมากครับ อธิบายละเอียด เข้าใจง่าย เหมาะสำหรับมือใหม่มากๆ แนะนำเลยครับ','pending','2025-11-19 14:30:30','2025-11-19 14:30:30'),(2,2,2,4,'เนื้อหาดี แต่บางส่วนอาจจะเร็วไปนิดนึง โดยรวมแล้วดีครับ','pending','2025-11-19 14:30:30','2025-11-19 14:30:30'),(3,2,3,5,'สุดยอดมากครับ เรียนจบแล้วสามารถทำโปรเจคได้เลย ผู้สอนสอนดีมาก','pending','2025-11-19 14:30:30','2025-11-19 14:30:30'),(4,3,1,5,'คอร์สนี้ดีมากค่ะ เนื้อหาครบถ้วน ตัวอย่างชัดเจน เรียนแล้วเข้าใจง่าย','pending','2025-11-19 14:30:30','2025-11-19 14:30:30'),(5,3,4,4,'เนื้อหาดีค่ะ แต่อยากให้มีตัวอย่างเพิ่มอีกนิดนึง','pending','2025-11-19 14:30:30','2025-11-19 14:30:30'),(6,3,6,5,'Python เรียนง่ายมากค่ะ ผู้สอนอธิบายดี มีตัวอย่างเยอะ','pending','2025-11-19 14:30:30','2025-11-19 14:30:30'),(7,3,2,5,'JavaScript เรียนแล้วสนุกมากค่ะ เข้าใจง่าย','pending','2025-11-19 14:30:30','2025-11-19 14:30:30'),(8,2,5,4,'PHP เนื้อหาดีครับ แต่อยากให้อัพเดทเนื้อหาใหม่ๆ','pending','2025-11-19 14:30:30','2025-11-19 14:30:30'),(9,3,7,5,'Database Design สอนดีมากค่ะ เข้าใจหลักการออกแบบ','pending','2025-11-19 14:30:30','2025-11-19 14:30:30'),(10,2,8,5,'Flutter สุดยอดครับ สามารถทำแอปได้จริง','pending','2025-11-19 14:30:30','2025-11-19 14:30:30');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sections`
--

DROP TABLE IF EXISTS `sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sections` (
  `section_id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `section_title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `section_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`section_id`),
  KEY `idx_course` (`course_id`),
  CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sections`
--

LOCK TABLES `sections` WRITE;
/*!40000 ALTER TABLE `sections` DISABLE KEYS */;
INSERT INTO `sections` VALUES (1,1,'บทนำและพื้นฐาน HTML',1,'2025-11-19 14:27:07'),(2,1,'CSS Styling',2,'2025-11-19 14:27:07'),(3,1,'Responsive Design',3,'2025-11-19 14:27:07'),(4,1,'บทนำและพื้นฐาน HTML',1,'2025-11-19 14:28:16'),(5,1,'CSS Styling',2,'2025-11-19 14:28:16'),(6,1,'Responsive Design',3,'2025-11-19 14:28:16'),(7,2,'JavaScript พื้นฐาน',1,'2025-11-19 14:28:16'),(8,2,'DOM Manipulation',2,'2025-11-19 14:28:16'),(9,2,'Async JavaScript',3,'2025-11-19 14:28:16'),(10,3,'React Basics',1,'2025-11-19 14:28:16'),(11,3,'Components และ Props',2,'2025-11-19 14:28:16'),(12,3,'State Management',3,'2025-11-19 14:28:16'),(13,4,'Node.js พื้นฐาน',1,'2025-11-19 14:28:16'),(14,4,'Express Framework',2,'2025-11-19 14:28:16'),(15,5,'PHP Basics',1,'2025-11-19 14:28:16'),(16,5,'MySQL Database',2,'2025-11-19 14:28:16'),(17,6,'Python Fundamentals',1,'2025-11-19 14:28:16'),(18,6,'Python Advanced',2,'2025-11-19 14:28:16'),(19,7,'Database Design',1,'2025-11-19 14:28:16'),(20,7,'SQL Queries',2,'2025-11-19 14:28:16'),(21,8,'Flutter Basics',1,'2025-11-19 14:28:16'),(22,8,'Flutter Widgets',2,'2025-11-19 14:28:16');
/*!40000 ALTER TABLE `sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `profile_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('student','instructor','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'student',
  `status` enum('active','inactive','banned') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin@devshop.com','$2y$10$KfKuQbelmi3X3RwANOWdJexctNKOFrSxGJWqr8Hqu/xR7hGrUBp0C','Admin','DevShop','0812345678',NULL,NULL,'admin','active','2025-11-19 14:14:43','2025-11-19 14:17:57'),(2,'user1@test.com','$2y$10$KfKuQbelmi3X3RwANOWdJexctNKOFrSxGJWqr8Hqu/xR7hGrUBp0C','สมชาย','ใจดี','0823456789',NULL,NULL,'student','active','2025-11-19 14:14:43','2025-11-19 14:17:57'),(3,'user2@test.com','$2y$10$KfKuQbelmi3X3RwANOWdJexctNKOFrSxGJWqr8Hqu/xR7hGrUBp0C','สมหญิง','รักเรียน','0834567890',NULL,NULL,'student','active','2025-11-19 14:14:43','2025-11-19 14:17:57');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlist`
--

DROP TABLE IF EXISTS `wishlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wishlist` (
  `wishlist_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `course_id` int NOT NULL,
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`wishlist_id`),
  UNIQUE KEY `unique_wishlist` (`user_id`,`course_id`),
  KEY `course_id` (`course_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlist`
--

LOCK TABLES `wishlist` WRITE;
/*!40000 ALTER TABLE `wishlist` DISABLE KEYS */;
/*!40000 ALTER TABLE `wishlist` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-20 18:32:55
