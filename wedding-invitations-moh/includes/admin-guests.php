<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$guests_table = $wpdb->prefix . 'wi_guests';
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
$guest_id = isset($_GET['guest_id']) ? intval($_GET['guest_id']) : 0;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_admin_referer('wi_guest_nonce');
    
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $category = sanitize_text_field($_POST['category']);
    $plus_one_allowed = isset($_POST['plus_one_allowed']) ? 1 : 0;
    $notes = sanitize_textarea_field($_POST['notes']);
    
    if ($action === 'add') {
        $result = $wpdb->insert($guests_table, array(
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'category' => $category,
            'plus_one_allowed' => $plus_one_allowed,
            'notes' => $notes
        ));
        
        if ($result) {
            echo '<div class="notice notice-success"><p>تم إضافة المدعو بنجاح!</p></div>';
            $action = 'list';
        }
    } elseif ($action === 'edit' && $guest_id) {
        $result = $wpdb->update($guests_table, array(
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'category' => $category,
            'plus_one_allowed' => $plus_one_allowed,
            'notes' => $notes
        ), array('id' => $guest_id));
        
        if ($result !== false) {
            echo '<div class="notice notice-success"><p>تم تحديث بيانات المدعو بنجاح!</p></div>';
            $action = 'list';
        }
    }
}

// Handle delete action
if ($action === 'delete' && $guest_id && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_guest_' . $guest_id)) {
    $wpdb->delete($guests_table, array('id' => $guest_id));
    echo '<div class="notice notice-success"><p>تم حذف المدعو بنجاح!</p></div>';
    $action = 'list';
}
?>

<div class="wrap" dir="rtl">
    <h1 class="wp-heading-inline">المدعوين</h1>
    
    <?php if ($action === 'list'): ?>
        <a href="<?php echo admin_url('admin.php?page=wi-guests&action=add'); ?>" class="page-title-action">إضافة مدعو جديد</a>
        
        <?php
        $guests = $wpdb->get_results("SELECT * FROM $guests_table ORDER BY name ASC");
        ?>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>البريد الإلكتروني</th>
                    <th>الهاتف</th>
                    <th>الفئة</th>
                    <th>مرافق مسموح</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($guests as $guest): ?>
                    <tr>
                        <td><strong><?php echo esc_html($guest->name); ?></strong></td>
                        <td><?php echo esc_html($guest->email); ?></td>
                        <td><?php echo esc_html($guest->phone); ?></td>
                        <td><?php echo esc_html($guest->category); ?></td>
                        <td><?php echo $guest->plus_one_allowed ? '✅ نعم' : '❌ لا'; ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=wi-guests&action=edit&guest_id=' . $guest->id); ?>">تعديل</a> |
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=wi-guests&action=delete&guest_id=' . $guest->id), 'delete_guest_' . $guest->id); ?>" 
                               onclick="return confirm('هل أنت متأكد من حذف هذا المدعو؟')">حذف</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if (empty($guests)): ?>
            <p>لا توجد مدعوين بعد. <a href="<?php echo admin_url('admin.php?page=wi-guests&action=add'); ?>">إضافة أول مدعو</a></p>
        <?php endif; ?>
        
    <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <?php
        $guest = null;
        if ($action === 'edit' && $guest_id) {
            $guest = $wpdb->get_row($wpdb->prepare("SELECT * FROM $guests_table WHERE id = %d", $guest_id));
        }
        ?>
        
        <h2><?php echo $action === 'add' ? 'إضافة مدعو جديد' : 'تعديل المدعو'; ?></h2>
        
        <form method="post" action="">
            <?php wp_nonce_field('wi_guest_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th><label for="name">الاسم *</label></th>
                    <td><input type="text" name="name" id="name" value="<?php echo $guest ? esc_attr($guest->name) : ''; ?>" required class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="email">البريد الإلكتروني</label></th>
                    <td><input type="email" name="email" id="email" value="<?php echo $guest ? esc_attr($guest->email) : ''; ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="phone">الهاتف</label></th>
                    <td><input type="tel" name="phone" id="phone" value="<?php echo $guest ? esc_attr($guest->phone) : ''; ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="category">الفئة</label></th>
                    <td>
                        <select name="category" id="category">
                            <option value="">اختر الفئة</option>
                            <option value="family" <?php echo ($guest && $guest->category === 'family') ? 'selected' : ''; ?>>العائلة</option>
                            <option value="friends" <?php echo ($guest && $guest->category === 'friends') ? 'selected' : ''; ?>>الأصدقاء</option>
                            <option value="colleagues" <?php echo ($guest && $guest->category === 'colleagues') ? 'selected' : ''; ?>>الزملاء</option>
                            <option value="vip" <?php echo ($guest && $guest->category === 'vip') ? 'selected' : ''; ?>>شخصيات مهمة</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="plus_one_allowed">مرافق مسموح</label></th>
                    <td>
                        <input type="checkbox" name="plus_one_allowed" id="plus_one_allowed" value="1" 
                               <?php echo ($guest && $guest->plus_one_allowed) ? 'checked' : ''; ?>>
                        <label for="plus_one_allowed">السماح بإحضار مرافق</label>
                    </td>
                </tr>
                <tr>
                    <th><label for="notes">ملاحظات</label></th>
                    <td><textarea name="notes" id="notes" rows="3" class="large-text"><?php echo $guest ? esc_textarea($guest->notes) : ''; ?></textarea></td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" class="button button-primary" value="<?php echo $action === 'add' ? 'إضافة المدعو' : 'تحديث المدعو'; ?>">
                <a href="<?php echo admin_url('admin.php?page=wi-guests'); ?>" class="button">إلغاء</a>
            </p>
        </form>
        
    <?php endif; ?>
</div>
