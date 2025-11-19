<?php
session_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/course_functions.php';
require_once 'includes/cart_functions.php';

// รับคำค้นหา
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$search_results = [];
$total_results = 0;

// รับการเรียงลำดับ
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// ค้นหาคอร์ส
if (!empty($search_query)) {
    $search_query_escaped = $conn->real_escape_string($search_query);
    
    // กำหนดการเรียงลำดับ
    $order_by = "c.created_at DESC";
    switch ($sort_by) {
        case 'popular':
            $order_by = "total_students DESC, c.created_at DESC";
            break;
        case 'price-low':
            $order_by = "COALESCE(c.discount_price, c.price) ASC";
            break;
        case 'price-high':
            $order_by = "COALESCE(c.discount_price, c.price) DESC";
            break;
        default:
            $order_by = "c.created_at DESC";
    }
    
    $sql = "SELECT c.*, cat.category_name, cat.icon,
            CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
            COALESCE(AVG(r.rating), 0) as avg_rating,
            COUNT(DISTINCT e.enrollment_id) as total_students
            FROM courses c
            LEFT JOIN categories cat ON c.category_id = cat.category_id
            LEFT JOIN users u ON c.instructor_id = u.user_id
            LEFT JOIN reviews r ON c.course_id = r.course_id AND r.status = 'approved'
            LEFT JOIN enrollments e ON c.course_id = e.course_id
            WHERE c.status = 'published' 
            AND (
                c.course_title LIKE '%$search_query_escaped%' 
                OR c.description LIKE '%$search_query_escaped%'
                OR c.short_description LIKE '%$search_query_escaped%'
                OR cat.category_name LIKE '%$search_query_escaped%'
            )
            GROUP BY c.course_id
            ORDER BY $order_by";
    
    $result = $conn->query($sql);
    if ($result) {
        $search_results = $result->fetch_all(MYSQLI_ASSOC);
        $total_results = count($search_results);
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ค้นหา: <?php echo htmlspecialchars($search_query); ?> - DevShop</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="assets/css/search.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <!-- Search Header -->
    <section class="search-header">
        <div class="container">
            <?php if (!empty($search_query)): ?>
                <h1>ผลการค้นหา: <span class="search-query">"<?php echo htmlspecialchars($search_query); ?>"</span></h1>
                <p>พบ <strong><?php echo $total_results; ?></strong> คอร์สที่ตรงกับคำค้นหา</p>
            <?php else: ?>
                <h1>ค้นหาคอร์ส</h1>
                <p>กรุณากรอกคำค้นหาเพื่อค้นหาคอร์สที่คุณสนใจ</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Search Results -->
    <section class="search-container">
        <?php if (!empty($search_query)): ?>
            <?php if ($total_results > 0): ?>
                <div class="search-info">
                    <h2>พบ <span class="search-count"><?php echo $total_results; ?></span> คอร์ส</h2>
                    <div class="sort-options">
                        <label>เรียงตาม:</label>
                        <select onchange="sortResults(this.value)">
                            <option value="newest" <?php echo $sort_by == 'newest' ? 'selected' : ''; ?>>ใหม่ล่าสุด</option>
                            <option value="popular" <?php echo $sort_by == 'popular' ? 'selected' : ''; ?>>ยอดนิยม</option>
                            <option value="price-low" <?php echo $sort_by == 'price-low' ? 'selected' : ''; ?>>ราคาต่ำ-สูง</option>
                            <option value="price-high" <?php echo $sort_by == 'price-high' ? 'selected' : ''; ?>>ราคาสูง-ต่ำ</option>
                        </select>
                    </div>
                </div>

                <div class="courses-grid">
                    <?php foreach ($search_results as $course): ?>
                        <?php include 'includes/course_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>ไม่พบคอร์สที่ตรงกับคำค้นหา</h3>
                    <p>ลองค้นหาด้วยคำอื่น หรือดูคอร์สแนะนำของเรา</p>
                    <a href="courses.php" class="btn-primary">ดูคอร์สทั้งหมด</a>

                    <div class="search-suggestions">
                        <h4><i class="fas fa-lightbulb"></i> คำแนะนำ:</h4>
                        <ul>
                            <li><i class="fas fa-check"></i> ตรวจสอบการสะกดคำให้ถูกต้อง</li>
                            <li><i class="fas fa-check"></i> ลองใช้คำค้นหาที่สั้นกว่า หรือทั่วไปกว่า</li>
                            <li><i class="fas fa-check"></i> ลองค้นหาด้วยหมวดหมู่ เช่น "HTML", "JavaScript", "Python"</li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>กรุณากรอกคำค้นหา</h3>
                <p>ใช้ช่องค้นหาด้านบนเพื่อค้นหาคอร์สที่คุณสนใจ</p>
                <a href="courses.php" class="btn-primary">ดูคอร์สทั้งหมด</a>
            </div>
        <?php endif; ?>
    </section>

    <script>
        function sortResults(sortBy) {
            const url = new URL(window.location.href);
            url.searchParams.set('sort', sortBy);
            window.location.href = url.toString();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchQuery = '<?php echo addslashes($search_query); ?>';
            if (searchQuery) {
                const courseTitles = document.querySelectorAll('.course-card h3');
                courseTitles.forEach(title => {
                    const regex = new RegExp(`(${searchQuery})`, 'gi');
                    title.innerHTML = title.textContent.replace(regex, '<mark style="background: #ffd700; padding: 2px 4px; border-radius: 3px;">$1</mark>');
                });
            }
        });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>

</html>
