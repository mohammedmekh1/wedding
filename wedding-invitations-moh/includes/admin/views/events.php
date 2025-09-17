<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$events_table = $wpdb->prefix . 'wi_events';
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_admin_referer('wi_event_nonce');
    
    $name = sanitize_text_field($_POST['name']);
    $date_time = sanitize_text_field($_POST['date_time']);
    $venue = sanitize_text_field($_POST['venue']);
    $address = sanitize_textarea_field($_POST['address']);
    $description = sanitize_textarea_field($_POST['description']);
    $invitation_image_url = esc_url_raw($_POST['invitation_image_url']);
    $invitation_text = sanitize_textarea_field($_POST['invitation_text']);
    $location_details = sanitize_textarea_field($_POST['location_details']);
    
    if ($action === 'add') {
        $result = $wpdb->insert($events_table, array(
            'name' => $name,
            'date_time' => $date_time,
            'venue' => $venue,
            'address' => $address,
            'description' => $description,
            'invitation_image_url' => $invitation_image_url,
            'invitation_text' => $invitation_text,
            'location_details' => $location_details
        ));
        
        if ($result) {
            echo '<div class="notice notice-success"><p>تم إضافة المناسبة بنجاح!</p></div>';
            $action = 'list';
        }
    } elseif ($action === 'edit' && $event_id) {
        $result = $wpdb->update($events_table, array(
            'name' => $name,
            'date_time' => $date_time,
            'venue' => $venue,
            'address' => $address,
            'description' => $description,
            'invitation_image_url' => $invitation_image_url,
            'invitation_text' => $invitation_text,
            'location_details' => $location_details
        ), array('id' => $event_id));
        
        if ($result !== false) {
            echo '<div class="notice notice-success"><p>تم تحديث المناسبة بنجاح!</p></div>';
            $action = 'list';
        }
    }
}

// Handle delete action
if ($action === 'delete' && $event_id && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_event_' . $event_id)) {
    $wpdb->delete($events_table, array('id' => $event_id));
    echo '<div class="notice notice-success"><p>تم حذف المناسبة بنجاح!</p></div>';
    $action = 'list';
}
?>

<div class="wrap" dir="rtl">
    <h1 class="wp-heading-inline">المناسبات</h1>
    
    <?php if ($action === 'list'): ?>
        <a href="<?php echo admin_url('admin.php?page=wi-events&action=add'); ?>" class="page-title-action">إضافة مناسبة جديدة</a>
        
        <?php
        $events = $wpdb->get_results("SELECT * FROM $events_table ORDER BY date_time ASC");
        ?>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>اسم المناسبة</th>
                    <th>التاريخ والوقت</th>
                    <th>المكان</th>
                    <th>العنوان</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><strong><?php echo esc_html($event->name); ?></strong></td>
                        <td><?php echo date_i18n('Y/m/d H:i', strtotime($event->date_time)); ?></td>
                        <td><?php echo esc_html($event->venue); ?></td>
                        <td><?php echo esc_html($event->address); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=wi-events&action=edit&event_id=' . $event->id); ?>">تعديل</a> |
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=wi-events&action=delete&event_id=' . $event->id), 'delete_event_' . $event->id); ?>" 
                               onclick="return confirm('هل أنت متأكد من حذف هذه المناسبة؟')">حذف</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if (empty($events)): ?>
            <p>لا توجد مناسبات بعد. <a href="<?php echo admin_url('admin.php?page=wi-events&action=add'); ?>">إضافة أول مناسبة</a></p>
        <?php endif; ?>
        
    <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <?php
        $event = null;
        if ($action === 'edit' && $event_id) {
            $event = $wpdb->get_row($wpdb->prepare("SELECT * FROM $events_table WHERE id = %d", $event_id));
        }
        ?>
        
        <h2><?php echo $action === 'add' ? 'إضافة مناسبة جديدة' : 'تعديل المناسبة'; ?></h2>
        
        <form method="post" action="">
            <?php wp_nonce_field('wi_event_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th><label for="name">اسم المناسبة *</label></th>
                    <td><input type="text" name="name" id="name" value="<?php echo $event ? esc_attr($event->name) : ''; ?>" required class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="date_time">التاريخ والوقت *</label></th>
                    <td><input type="datetime-local" name="date_time" id="date_time" value="<?php echo $event ? esc_attr(date('Y-m-d\TH:i', strtotime($event->date_time))) : ''; ?>" required class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="venue">مكان المناسبة *</label></th>
                    <td><input type="text" name="venue" id="venue" value="<?php echo $event ? esc_attr($event->venue) : ''; ?>" required class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="address">العنوان</label></th>
                    <td><textarea name="address" id="address" rows="2" class="large-text"><?php echo $event ? esc_textarea($event->address) : ''; ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="description">وصف المناسبة</label></th>
                    <td><textarea name="description" id="description" rows="4" class="large-text"><?php echo $event ? esc_textarea($event->description) : ''; ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="invitation_image_url">رابط صورة الدعوة</label></th>
                    <td>
                        <input type="url" name="invitation_image_url" id="invitation_image_url" value="<?php echo $event ? esc_url($event->invitation_image_url) : ''; ?>" class="regular-text">
                        <p class="description">رابط الصورة التي ستظهر في الدعوة</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="invitation_text">نص الدعوة</label></th>
                    <td>
                        <textarea name="invitation_text" id="invitation_text" rows="3" class="large-text"><?php echo $event ? esc_textarea($event->invitation_text) : ''; ?></textarea>
                        <p class="description">النص الترحيبي الذي سيظهر في الدعوة</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="location_details">تفاصيل المكان</label></th>
                    <td>
                        <textarea name="location_details" id="location_details" rows="3" class="large-text"><?php echo $event ? esc_textarea($event->location_details) : ''; ?></textarea>
                        <p class="description">معلومات إضافية عن المكان</p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" class="button button-primary" value="<?php echo $action === 'add' ? 'إضافة المناسبة' : 'تحديث المناسبة'; ?>">
                <a href="<?php echo admin_url('admin.php?page=wi-events'); ?>" class="button">إلغاء</a>
            </p>
        </form>
        
    <?php endif; ?>
</div>
