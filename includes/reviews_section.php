<?php
require_once 'includes/review_functions.php';

$reviews = get_course_reviews($course_id, 10);
$rating_summary = get_course_rating_summary($course_id);
?>

<div class="reviews-section">
    <h2><i class="fas fa-star"></i> รีวิวจากผู้เรียน</h2>
    
    <?php if ($rating_summary && $rating_summary['total_reviews'] > 0): ?>
        <div class="rating-summary">
            <div class="rating-overview">
                <div class="rating-score">
                    <span class="score"><?php echo number_format($rating_summary['avg_rating'], 1); ?></span>
                    <div class="stars">
                        <?php echo render_stars(round($rating_summary['avg_rating'])); ?>
                    </div>
                    <p><?php echo $rating_summary['total_reviews']; ?> รีวิว</p>
                </div>
                <div class="rating-bars">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <?php 
                        $count = $rating_summary[['one_star', 'two_star', 'three_star', 'four_star', 'five_star'][$i-1]];
                        $percentage = $rating_summary['total_reviews'] > 0 ? ($count / $rating_summary['total_reviews']) * 100 : 0;
                        ?>
                        <div class="rating-bar">
                            <span class="star-label"><?php echo $i; ?> <i class="fas fa-star"></i></span>
                            <div class="bar">
                                <div class="fill" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                            <span class="count"><?php echo $count; ?></span>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <div class="reviews-list">
            <?php foreach ($reviews as $review): ?>
                <div class="review-item">
                    <div class="review-header">
                        <div class="reviewer-info">
                            <div class="reviewer-avatar">
                                <?php if ($review['profile_image']): ?>
                                    <img src="<?php echo htmlspecialchars($review['profile_image']); ?>" alt="<?php echo htmlspecialchars($review['first_name']); ?>">
                                <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <?php echo strtoupper(substr($review['first_name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h4><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></h4>
                                <div class="review-meta">
                                    <div class="stars">
                                        <?php echo render_stars($review['rating']); ?>
                                    </div>
                                    <span class="review-date"><?php echo time_ago($review['created_at']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="review-content">
                        <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-reviews">
            <i class="fas fa-comment-slash"></i>
            <p>ยังไม่มีรีวิวสำหรับคอร์สนี้</p>
        </div>
    <?php endif; ?>
</div>

<style>
.reviews-section {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    margin: 30px 0;
}

.reviews-section h2 {
    font-size: 24px;
    color: #2c3e50;
    margin-bottom: 30px;
}

.rating-summary {
    margin-bottom: 40px;
}

.rating-overview {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 40px;
    padding: 30px;
    background: #f8f9fa;
    border-radius: 12px;
}

.rating-score {
    text-align: center;
}

.rating-score .score {
    font-size: 48px;
    font-weight: 700;
    color: #2c3e50;
    display: block;
    margin-bottom: 10px;
}

.rating-score .stars {
    font-size: 20px;
    margin-bottom: 10px;
}

.rating-score p {
    color: #7f8c8d;
    margin: 0;
}

.rating-bars {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.rating-bar {
    display: flex;
    align-items: center;
    gap: 15px;
}

.star-label {
    min-width: 50px;
    font-size: 14px;
    color: #2c3e50;
}

.star-label i {
    color: #ffc107;
}

.bar {
    flex: 1;
    height: 8px;
    background: #ecf0f1;
    border-radius: 4px;
    overflow: hidden;
}

.bar .fill {
    height: 100%;
    background: #ffc107;
    transition: width 0.3s;
}

.count {
    min-width: 40px;
    text-align: right;
    color: #7f8c8d;
    font-size: 14px;
}

.reviews-list {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.review-item {
    padding: 25px;
    border: 1px solid #ecf0f1;
    border-radius: 12px;
    transition: all 0.3s;
}

.review-item:hover {
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.review-header {
    margin-bottom: 15px;
}

.reviewer-info {
    display: flex;
    gap: 15px;
    align-items: center;
}

.reviewer-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
}

.reviewer-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #667eea;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: 700;
}

.reviewer-info h4 {
    margin: 0 0 5px 0;
    font-size: 16px;
    color: #2c3e50;
}

.review-meta {
    display: flex;
    align-items: center;
    gap: 15px;
}

.review-meta .stars {
    font-size: 14px;
}

.review-date {
    color: #7f8c8d;
    font-size: 13px;
}

.review-content p {
    margin: 0;
    color: #2c3e50;
    line-height: 1.6;
}

.no-reviews {
    text-align: center;
    padding: 60px 20px;
    color: #7f8c8d;
}

.no-reviews i {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
}

.no-reviews p {
    font-size: 18px;
    margin: 0;
}

@media (max-width: 768px) {
    .rating-overview {
        grid-template-columns: 1fr;
        gap: 30px;
    }
}
</style>
