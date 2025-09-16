<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

$token = isset($_GET['wi_invitation']) ? sanitize_text_field($_GET['wi_invitation']) : 
         (isset($atts['token']) ? $atts['token'] : '');

if (empty($token)) {
    wp_die('رمز الدعوة غير صحيح');
}

// Get invitation data
$links_table = $wpdb->prefix . 'wi_invitation_links';
$guests_table = $wpdb->prefix . 'wi_guests';
$events_table = $wpdb->prefix . 'wi_events';
$rsvps_table = $wpdb->prefix . 'wi_rsvps';
$comments_table = $wpdb->prefix . 'wi_comments';

$invitation = $wpdb->get_row($wpdb->prepare("
    SELECT il.*, g.name as guest_name, e.*
    FROM $links_table il
    JOIN $guests_table g ON il.guest_id = g.id
    JOIN $events_table e ON il.event_id = e.id
    WHERE il.unique_token = %s
", $token));

if (!$invitation) {
    wp_die('رابط الدعوة غير صحيح');
}

// Get existing RSVP
$rsvp = $wpdb->get_row($wpdb->prepare("
    SELECT * FROM $rsvps_table 
    WHERE guest_id = %d AND event_id = %d
", $invitation->guest_id, $invitation->event_id));

// Get comments for this event
$comments = $wpdb->get_results($wpdb->prepare("
    SELECT * FROM $comments_table 
    WHERE event_id = %d AND is_visible = 1 
    ORDER BY created_at DESC
", $invitation->event_id));

$event_date = new DateTime($invitation->date_time);
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?> dir="rtl">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>دعوة <?php echo esc_html($invitation->name); ?> - <?php echo esc_html($invitation->guest_name); ?></title>
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #333;
            direction: rtl;
        }
        
        .invitation-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .invitation-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .invitation-image {
            width: 100%;
            max-width: 400px;
            height: auto;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .event-title {
            font-size: 2.5rem;
            color: #e91e63;
            margin: 10px 0;
        }
        
        .guest-name {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 20px;
        }
        
        .invitation-text {
            background: linear-gradient(135deg, #fce4ec 0%, #f3e5f5 100%);
            padding: 20px;
            border-radius: 10px;
            font-size: 1.1rem;
            line-height: 1.6;
            margin: 20px 0;
            text-align: center;
        }
        
        .event-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 30px 0;
        }
        
        .detail-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border-right: 4px solid #e91e63;
        }
        
        .detail-label {
            font-weight: bold;
            color: #666;
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-size: 1.1rem;
            color: #333;
        }
        
        .rsvp-section {
            background: #fff;
            border: 2px solid #e91e63;
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            text-align: center;
        }
        
        .rsvp-title {
            font-size: 1.5rem;
            color: #e91e63;
            margin-bottom: 20px;
        }
        
        .rsvp-options {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .rsvp-option {
            padding: 15px 25px;
            border: 2px solid #ddd;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }
        
        .rsvp-option:hover {
            border-color: #e91e63;
        }
        
        .rsvp-option.selected {
            border-color: #e91e63;
            background: #fce4ec;
        }
        
        .rsvp-button {
            background: linear-gradient(135deg, #e91e63 0%, #ad1457 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .rsvp-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(233, 30, 99, 0.3);
        }
        
        .confirmed-status {
            background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .qr-section {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .qr-code {
            width: 200px;
            height: 200px;
            margin: 20px auto;
        }
        
        .comments-section {
            margin-top: 40px;
        }
        
        .comments-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 20px;
        }
        
        .comment-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .comment-form input,
        .comment-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
            font-family: inherit;
        }
        
        .comment-item {
            background: white;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
        }
        
        .comment-author {
            font-weight: bold;
            color: #e91e63;
            margin-bottom: 5px;
        }
        
        .comment-date {
            color: #666;
            font-size: 0.9rem;
            float: left;
        }
        
        .comment-text {
            color: #333;
            line-height: 1.5;
        }
        
        @media (max-width: 600px) {
            .event-details {
                grid-template-columns: 1fr;
            }
            
            .rsvp-options {
                flex-direction: column;
                align-items: center;
            }
            
            .invitation-container {
                margin: 10px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="invitation-container">
        <!-- Header -->
        <div class="invitation-header">
            <?php if ($invitation->invitation_image_url): ?>
                <img src="<?php echo esc_url($invitation->invitation_image_url); ?>" 
                     alt="دعوة الزفاف" class="invitation-image">
            <?php endif; ?>
            
            <h1 class="guest-name">أهلاً وسهلاً <?php echo esc_html($invitation->guest_name); ?></h1>
            <h2 class="event-title"><?php echo esc_html($invitation->name); ?></h2>
        </div>
        
        <!-- Invitation Text -->
        <?php if ($invitation->invitation_text): ?>
            <div class="invitation-text">
                <?php echo nl2br(esc_html($invitation->invitation_text)); ?>
            </div>
        <?php endif; ?>
        
        <!-- Event Details -->
        <div class="event-details">
            <div class="detail-box">
                <div class="detail-label">📅 التاريخ</div>
                <div class="detail-value">
                    <?php echo $event_date->format('l، j F Y'); ?>
                </div>
            </div>
            
            <div class="detail-box">
                <div class="detail-label">🕐 الوقت</div>
                <div class="detail-value">
                    <?php echo $event_date->format('h:i A'); ?>
                </div>
            </div>
            
            <div class="detail-box">
                <div class="detail-label">📍 المكان</div>
                <div class="detail-value"><?php echo esc_html($invitation->venue); ?></div>
            </div>
            
            <div class="detail-box">
                <div class="detail-label">🏠 العنوان</div>
                <div class="detail-value"><?php echo esc_html($invitation->address); ?></div>
            </div>
        </div>
        
        <!-- RSVP Section -->
        <div class="rsvp-section">
            <?php if (!$rsvp || !$rsvp->is_confirmed): ?>
                <h3 class="rsvp-title">يرجى تأكيد الحضور</h3>
                
                <form id="rsvp-form" method="post">
                    <div class="rsvp-options">
                        <div class="rsvp-option" data-status="accepted">
                            <div>✅ سأحضر</div>
                        </div>
                        <div class="rsvp-option" data-status="declined">
                            <div>❌ لن أحضر</div>
                        </div>
                        <div class="rsvp-option" data-status="pending">
                            <div>⏳ معتذر</div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="action" value="wi_rsvp">
                    <input type="hidden" name="token" value="<?php echo esc_attr($token); ?>">
                    <input type="hidden" name="status" id="selected-status" value="">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('wi_nonce'); ?>">
                    
                    <button type="submit" class="rsvp-button" id="rsvp-submit" disabled>تأكيد الرد</button>
                </form>
            <?php else: ?>
                <div class="confirmed-status">
                    ✅ تم تسجيل ردك: 
                    <?php 
                    echo $rsvp->status === 'accepted' ? 'سأحضر' : 
                         ($rsvp->status === 'declined' ? 'لن أحضر' : 'معتذر');
                    ?>
                </div>
                
                <?php if ($rsvp->status === 'accepted' && $invitation->qr_code_url): ?>
                    <div class="qr-section">
                        <h4>رمز الحضور الخاص بك</h4>
                        <img src="<?php echo esc_url($invitation->qr_code_url); ?>" 
                             alt="QR Code" class="qr-code">
                        <p>احتفظ بهذا الرمز وأحضره معك يوم المناسبة</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- Comments Section -->
        <div class="comments-section">
            <h3 class="comments-title">كتابة التهاني</h3>
            
            <!-- Add Comment Form -->
            <form id="comment-form" class="comment-form">
                <input type="text" name="author_name" placeholder="اسمك" required>
                <textarea name="comment_text" placeholder="اكتب تهنئتك هنا..." rows="3" required></textarea>
                <input type="hidden" name="event_id" value="<?php echo $invitation->event_id; ?>">
                <button type="submit" class="rsvp-button">إضافة تهنئة</button>
            </form>
            
            <!-- Comments List -->
            <div id="comments-list">
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-item">
                        <div class="comment-author"><?php echo esc_html($comment->author_name); ?></div>
                        <div class="comment-date"><?php echo date_i18n('Y/m/d', strtotime($comment->created_at)); ?></div>
                        <div class="comment-text"><?php echo nl2br(esc_html($comment->comment_text)); ?></div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($comments)): ?>
                    <p style="text-align: center; color: #666;">لا توجد تهاني بعد. كن أول من يهنئ!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // RSVP functionality
        document.querySelectorAll('.rsvp-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.rsvp-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('selected-status').value = this.dataset.status;
                document.getElementById('rsvp-submit').disabled = false;
            });
        });
        
        document.getElementById('rsvp-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('تم حفظ ردك بنجاح!');
                    location.reload();
                } else {
                    alert(data.data || 'حدث خطأ في حفظ الرد');
                }
            })
            .catch(error => {
                alert('حدث خطأ في الاتصال');
            });
        });
        
        // Comments functionality
        document.getElementById('comment-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'wi_add_comment');
            formData.append('nonce', '<?php echo wp_create_nonce('wi_nonce'); ?>');
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.reset();
                    location.reload();
                } else {
                    alert('حدث خطأ في إضافة التعليق');
                }
            });
        });
    </script>
</body>
</html>
