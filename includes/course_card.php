<?php
$course_url = 'course_detail.php?id=' . $course['course_id'];
?>
<div class="course-card">
    <div class="course-image">
        <a href="<?php echo $course_url; ?>">
            <?php 
            $thumbnail = !empty($course['thumbnail']) ? htmlspecialchars($course['thumbnail']) : 'https://via.placeholder.com/400x250/667eea/ffffff?text=' . urlencode($course['course_title']);
            ?>
            <img src="<?php echo $thumbnail; ?>" alt="<?php echo htmlspecialchars($course['course_title']); ?>">
        </a>
        
        <!-- ปุ่มถูกใจซ้ายบน -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="course-wishlist-btn-left">
                <a href="add_to_wishlist.php?course_id=<?php echo $course['course_id']; ?>" 
                   class="wishlist-icon" title="เพิ่มลงรายการถูกใจ">
                    <i class="fas fa-heart"></i>
                </a>
            </div>
        <?php endif; ?>
        
        <!-- Badge ขวาบน -->
        <div class="course-badge-right">
            <?php echo get_course_badge($course); ?>
        </div>
    </div>
    
    <div class="course-content">
        <!-- Instructor -->
        <div class="course-instructor">
            <i class="fas fa-user"></i> <?php echo htmlspecialchars($course['instructor_name']); ?>
        </div>
        
        <!-- Title -->
        <a href="<?php echo $course_url; ?>">
            <h3 class="course-title"><?php echo htmlspecialchars($course['course_title']); ?></h3>
        </a>
        
        <!-- Description -->
        <p class="course-description"><?php echo htmlspecialchars($course['short_description']); ?></p>
        
        <!-- Rating Stars -->
        <div class="course-rating-stars">
            <?php 
            $rating = $course['avg_rating'];
            for ($i = 1; $i <= 5; $i++): 
                if ($i <= $rating): ?>
                    <i class="fas fa-star"></i>
                <?php else: ?>
                    <i class="far fa-star"></i>
                <?php endif;
            endfor; ?>
            <span class="rating-number"><?php echo number_format($rating, 1); ?></span>
        </div>
        
        <!-- Info -->
        <div class="course-info-row">
            <span><i class="fas fa-clock"></i> <?php echo $course['duration_hours']; ?> ชั่วโมง</span>
            <span><i class="fas fa-signal"></i> <?php echo get_level_text($course['level']); ?></span>
        </div>
        
        <!-- Price -->
        <div class="course-price-large">
            <?php if ($course['discount_price'] && $course['discount_price'] < $course['price']): ?>
                <div class="price-old">฿<?php echo format_price($course['price']); ?></div>
                <div class="price-new">฿<?php echo format_price($course['discount_price']); ?></div>
            <?php else: ?>
                <div class="price-new">฿<?php echo format_price($course['price']); ?></div>
            <?php endif; ?>
        </div>
        
        <!-- Buttons -->
        <div class="course-buttons-row">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="add_to_cart.php?course_id=<?php echo $course['course_id']; ?>" 
                   class="btn-add-cart-primary">
                    <i class="fas fa-shopping-cart"></i> เพิ่มลงตะกร้า
                </a>
            <?php endif; ?>
            <a href="<?php echo $course_url; ?>" class="btn-view-detail-outline">
                <i class="fas fa-info-circle"></i> ดูรายละเอียด
            </a>
        </div>
    </div>
</div>
