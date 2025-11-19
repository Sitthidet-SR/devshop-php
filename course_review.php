<?php
session_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth_functions.php';

if (!is_logged_in()) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['course_id'])) {
    header('Location: my_courses.php');
    exit;
}

$course_id = intval($_GET['course_id']);

$enrollment_check = $conn->query("SELECT * FROM enrollments WHERE user_id = $user_id AND course_id = $course_id");
if ($enrollment_check->num_rows == 0) {
    $_SESSION['error'] = 'คุณต้องซื้อคอร์สนี้ก่อนจึงจะรีวิวได้';
    header('Location: courses.php');
    exit;
}

$course = $conn->query("SELECT * FROM courses WHERE course_id = $course_id")->fetch_assoc();

$existing_review = $conn->query("SELECT * FROM reviews WHERE user_id = $user_id AND course_id = $course_id")->fetch_assoc();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = intval($_POST['rating']);
    $comment = clean_input($_POST['comment']);

    if ($rating < 1 || $rating > 5) {
        $error = 'กรุณาให้คะแนน 1-5 ดาว';
    } elseif (empty($comment)) {
        $error = 'กรุณาเขียนความคิดเห็น';
    } else {
        if ($existing_review) {
            $sql = "UPDATE reviews SET rating = $rating, comment = '$comment', status = 'pending' WHERE review_id = {$existing_review['review_id']}";
            $msg_code = 'review_updated';
        } else {
            $sql = "INSERT INTO reviews (user_id, course_id, rating, comment, status) VALUES ($user_id, $course_id, $rating, '$comment', 'pending')";
            $msg_code = 'review_submitted';
        }

        if ($conn->query($sql)) {
            require_once 'includes/redirect_helper.php';
            redirect_with_message('my_courses.php', $msg_code);
        } else {
            $error = 'เกิดข้อผิดพลาด: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รีวิวคอร์ส - <?php echo htmlspecialchars($course['course_title']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/sweetalert-helper.js?v=<?php echo time(); ?>"></script>
    <style>
        .review-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .review-box {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .course-info {
            display: flex;
            gap: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .course-info img {
            width: 150px;
            height: 90px;
            object-fit: cover;
            border-radius: 8px;
        }

        .course-info h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }

        .rating-input {
            margin: 30px 0;
        }

        .rating-input label {
            display: block;
            margin-bottom: 15px;
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
        }

        .stars {
            display: flex;
            gap: 10px;
            font-size: 40px;
        }

        .stars i {
            cursor: pointer;
            color: #ddd;
            transition: all 0.3s;
        }

        .stars i:hover,
        .stars i.active {
            color: #ffc107;
            transform: scale(1.2);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: 15px;
            min-height: 150px;
            resize: vertical;
            font-family: inherit;
        }

        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-submit {
            background: #667eea;
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .existing-review {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #2196f3;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="review-container">
        <div class="review-box">
            <h2><i class="fas fa-star"></i> รีวิวคอร์ส</h2>

            <div class="course-info">
                <img src="<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="<?php echo htmlspecialchars($course['course_title']); ?>">
                <div>
                    <h3><?php echo htmlspecialchars($course['course_title']); ?></h3>
                    <p style="color: #7f8c8d; margin: 0;">แชร์ประสบการณ์การเรียนของคุณ</p>
                </div>
            </div>

            <?php if ($existing_review): ?>
                <div class="existing-review">
                    <strong><i class="fas fa-info-circle"></i> คุณเคยรีวิวคอร์สนี้แล้ว</strong>
                    <p style="margin: 5px 0 0 0;">คุณสามารถแก้ไขรีวิวของคุณได้</p>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div id="php-alert-data" 
                     data-type="error" 
                     data-message="<?php echo htmlspecialchars($error); ?>" 
                     style="display:none;"></div>
            <?php endif; ?>

            <form method="POST">
                <div class="rating-input">
                    <label>ให้คะแนน *</label>
                    <div class="stars">
                        <i class="fas fa-star" data-rating="1"></i>
                        <i class="fas fa-star" data-rating="2"></i>
                        <i class="fas fa-star" data-rating="3"></i>
                        <i class="fas fa-star" data-rating="4"></i>
                        <i class="fas fa-star" data-rating="5"></i>
                    </div>
                    <input type="hidden" name="rating" id="rating" value="<?php echo $existing_review ? $existing_review['rating'] : '0'; ?>" required>
                </div>

                <div class="form-group">
                    <label>ความคิดเห็น *</label>
                    <textarea name="comment" placeholder="แชร์ประสบการณ์การเรียนของคุณ..." required><?php echo $existing_review ? htmlspecialchars($existing_review['comment']) : ''; ?></textarea>
                </div>

                <div>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> <?php echo $existing_review ? 'อัพเดทรีวิว' : 'ส่งรีวิว'; ?>
                    </button>
                    <a href="my_courses.php" class="btn-cancel">
                        <i class="fas fa-times"></i> ยกเลิก
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const stars = document.querySelectorAll('.stars i');
        const ratingInput = document.getElementById('rating');
        const currentRating = parseInt(ratingInput.value);

        if (currentRating > 0) {
            for (let i = 0; i < currentRating; i++) {
                stars[i].classList.add('active');
            }
        }

        stars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.getAttribute('data-rating');
                ratingInput.value = rating;

                stars.forEach(s => s.classList.remove('active'));

                for (let i = 0; i < rating; i++) {
                    stars[i].classList.add('active');
                }
            });

            star.addEventListener('mouseenter', function() {
                const rating = this.getAttribute('data-rating');
                stars.forEach(s => s.classList.remove('active'));
                for (let i = 0; i < rating; i++) {
                    stars[i].classList.add('active');
                }
            });
        });

        document.querySelector('.stars').addEventListener('mouseleave', function() {
            const currentRating = parseInt(ratingInput.value);
            stars.forEach(s => s.classList.remove('active'));
            if (currentRating > 0) {
                for (let i = 0; i < currentRating; i++) {
                    stars[i].classList.add('active');
                }
            }
        });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
