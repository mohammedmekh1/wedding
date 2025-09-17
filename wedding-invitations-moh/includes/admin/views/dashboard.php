<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Get statistics
$guests_table = $wpdb->prefix . 'wi_guests';
$events_table = $wpdb->prefix . 'wi_events';
$rsvps_table = $wpdb->prefix . 'wi_rsvps';

$total_guests = $wpdb->get_var("SELECT COUNT(*) FROM $guests_table");
$total_events = $wpdb->get_var("SELECT COUNT(*) FROM $events_table");
$total_rsvps = $wpdb->get_var("SELECT COUNT(*) FROM $rsvps_table WHERE is_confirmed = 1");
$accepted_rsvps = $wpdb->get_var("SELECT COUNT(*) FROM $rsvps_table WHERE status = 'accepted' AND is_confirmed = 1");
?>

<div class="wrap" dir="rtl">
    <h1 class="wp-heading-inline">دعوات الزفاف - لوحة التحكم</h1>
    
    <div class="wi-dashboard-stats">
        <div class="wi-stat-box">
            <div class="wi-stat-icon">👥</div>
            <div class="wi-stat-content">
                <h3><?php echo $total_guests; ?></h3>
                <p>إجمالي المدعوين</p>
            </div>
        </div>
        
        <div class="wi-stat-box">
            <div class="wi-stat-icon">🎉</div>
            <div class="wi-stat-content">
                <h3><?php echo $total_events; ?></h3>
                <p>المناسبات</p>
            </div>
        </div>
        
        <div class="wi-stat-box">
            <div class="wi-stat-icon">📝</div>
            <div class="wi-stat-content">
                <h3><?php echo $total_rsvps; ?></h3>
                <p>الردود المؤكدة</p>
            </div>
        </div>
        
        <div class="wi-stat-box">
            <div class="wi-stat-icon">✅</div>
            <div class="wi-stat-content">
                <h3><?php echo $accepted_rsvps; ?></h3>
                <p>سيحضرون</p>
            </div>
        </div>
    </div>
    
    <div class="wi-dashboard-actions">
        <h2>الإجراءات السريعة</h2>
        <div class="wi-action-buttons">
            <a href="<?php echo admin_url('admin.php?page=wi-guests&action=add'); ?>" class="button button-primary">
                إضافة مدعو جديد
            </a>
            <a href="<?php echo admin_url('admin.php?page=wi-events&action=add'); ?>" class="button button-primary">
                إضافة مناسبة جديدة
            </a>
            <a href="<?php echo admin_url('admin.php?page=wi-invitations'); ?>" class="button button-secondary">
                إنشاء روابط الدعوات
            </a>
        </div>
    </div>
    
    <?php
    // Recent RSVPs
    $recent_rsvps = $wpdb->get_results("
        SELECT r.*, g.name as guest_name, e.name as event_name 
        FROM $rsvps_table r
        JOIN $guests_table g ON r.guest_id = g.id
        JOIN $events_table e ON r.event_id = e.id
        WHERE r.is_confirmed = 1
        ORDER BY r.response_date DESC
        LIMIT 10
    ");
    ?>
    
    <div class="wi-recent-activity">
        <h2>النشاط الأخير</h2>
        <?php if ($recent_rsvps): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>المدعو</th>
                        <th>المناسبة</th>
                        <th>الرد</th>
                        <th>تاريخ الرد</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_rsvps as $rsvp): ?>
                        <tr>
                            <td><?php echo esc_html($rsvp->guest_name); ?></td>
                            <td><?php echo esc_html($rsvp->event_name); ?></td>
                            <td>
                                <span class="wi-status wi-status-<?php echo $rsvp->status; ?>">
                                    <?php 
                                    echo $rsvp->status === 'accepted' ? 'سيحضر' : 
                                         ($rsvp->status === 'declined' ? 'لن يحضر' : 'معتذر');
                                    ?>
                                </span>
                            </td>
                            <td><?php echo date_i18n('Y/m/d H:i', strtotime($rsvp->response_date)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>لا توجد ردود بعد.</p>
        <?php endif; ?>
    </div>
</div>

<style>
.wi-dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.wi-stat-box {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
}

.wi-stat-icon {
    font-size: 2.5rem;
}

.wi-stat-content h3 {
    font-size: 2rem;
    margin: 0;
    color: #2271b1;
}

.wi-stat-content p {
    margin: 5px 0 0 0;
    color: #666;
}

.wi-dashboard-actions {
    margin: 30px 0;
}

.wi-action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.wi-recent-activity {
    margin-top: 30px;
}

.wi-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.wi-status-accepted {
    background: #d4edda;
    color: #155724;
}

.wi-status-declined {
    background: #f8d7da;
    color: #721c24;
}

.wi-status-pending {
    background: #fff3cd;
    color: #856404;
}
</style>
