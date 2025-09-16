# إضافة إدارة دعوات الزفاف لـ WordPress

نظام شامل لإدارة دعوات الزفاف والمناسبات مع واجهة حديثة وسهلة الاستخدام، مصمم خصيصاً للمواقع العربية.

## 🌟 الميزات الرئيسية

### 📝 إدارة المناسبات
- إنشاء وإدارة مناسبات متعددة
- تحديد تاريخ ووقت ومكان المناسبة
- إضافة وصف تفصيلي للمناسبة
- تتبع حالة المناسبة (نشطة، معطلة، مكتملة)

### 👥 إدارة المدعوين
- إضافة المدعوين مع تفاصيلهم الكاملة
- إنشاء أكواد دعوة فريدة تلقائياً
- تتبع حالة الرد (في الانتظار، سيحضر، لن يحضر)
- دعم إضافة شخص مرافق (+1)
- تصدير قوائم المدعوين

### 💌 نظام الدعوات
- إنشاء صفحات دعوة جميلة وتفاعلية
- تصميم متجاوب يعمل على جميع الأجهزة
- دعم كامل للغة العربية (RTL)
- إمكانية تخصيص تصميم الدعوة

### 📊 تتبع الردود (RSVP)
- نظام رد تفاعلي للمدعوين
- تتبع الردود في الوقت الفعلي
- إمكانية إضافة رسائل شخصية
- تقارير مفصلة عن حالة الردود

## 🚀 التثبيت

### المتطلبات
- WordPress 5.0 أو أحدث
- PHP 7.4 أو أحدث
- MySQL 5.6 أو أحدث

### طريقة التثبيت

#### 1. التثبيت عبر لوحة تحكم WordPress
1. اذهب إلى **الإضافات > إضافة جديد**
2. اختر **رفع إضافة**
3. حدد ملف `wedding-invitations.zip`
4. اضغط **تثبيت الآن**
5. فعّل الإضافة

#### 2. التثبيت اليدوي عبر FTP
1. فك ضغط ملف `wedding-invitations.zip`
2. ارفع مجلد `wedding-invitations` إلى `/wp-content/plugins/`
3. فعّل الإضافة من **الإضافات > الإضافات المثبتة**

#### 3. التثبيت عبر Composer (للمطورين)
```bash
composer require wedding-invitations/wp-plugin
```

## ⚙️ الإعداد الأولي

### 1. الوصول لإعدادات الإضافة
بعد التفعيل، ستجد قائمة جديدة **دعوات الزفاف** في لوحة تحكم WordPress.

### 2. إنشاء مناسبة جديدة
1. اذهب إلى **دعوات الزفاف > المناسبات**
2. اضغط **إضافة مناسبة جديدة**
3. املأ التفاصيل:
   - عنوان المناسبة
   - الوصف
   - التاريخ والوقت
   - المكان
4. احفظ المناسبة

### 3. إضافة المدعوين
1. اذهب إلى **دعوات الزفاف > المدعوين**
2. اختر المناسبة
3. اضغط **إضافة مدعو جديد**
4. املأ بيانات المدعو:
   - الاسم الكامل
   - البريد الإلكتروني
   - رقم الهاتف
5. احفظ البيانات (سيتم إنشاء كود دعوة تلقائياً)

## 📖 دليل الاستخدام

### إنشاء صفحة الدعوة

#### استخدام Shortcode
```php
[wedding_invitation code="INVITATION_CODE"]
```

#### في ملفات القوالب
```php
<?php echo do_shortcode('[wedding_invitation code="' . $invitation_code . '"]'); ?>
```

### نموذج الرد على الدعوة
```php
[rsvp_form guest_id="123"]
```

### عرض قائمة المناسبات
```php
<?php
$events = WI_Database::get_events();
foreach ($events as $event) {
    echo '<h3>' . esc_html($event->title) . '</h3>';
    echo '<p>' . esc_html($event->description) . '</p>';
}
?>
```

## 🎨 التخصيص

### تخصيص التصميم
يمكنك تخصيص تصميم الدعوات عبر:

#### 1. CSS مخصص
```css
.wi-invitation-container {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.wi-invitation-title {
    font-family: 'Amiri', serif;
    color: #2c3e50;
}
```

#### 2. إنشاء قوالب مخصصة
إنشاء ملف `invitation-template.php` في قالبك:
```php
<?php
// قالب دعوة مخصص
$guest = WI_Database::get_guest_by_code($atts['code']);
?>
<div class="custom-invitation">
    <h1>أهلاً وسهلاً <?php echo esc_html($guest->name); ?></h1>
    <!-- محتوى الدعوة المخصص -->
</div>
```

## 🔧 للمطورين

### Structure الملفات
```
wedding-invitations/
├── admin/              # ملفات لوحة التحكم
│   ├── css/           
│   ├── js/            
│   └── images/        
├── public/            # ملفات الواجهة الأمامية
│   ├── css/           
│   ├── js/            
│   └── images/        
├── includes/          # ملفات PHP الأساسية
│   ├── admin/         
│   ├── public/        
│   └── migrations/    
├── templates/         # قوالب الدعوات
├── languages/         # ملفات الترجمة
└── vendor/           # مكتبات Composer
```

### Hooks والـ Filters المتاحة

#### Actions
```php
// قبل عرض الدعوة
do_action('wi_before_invitation_display', $guest_data);

// بعد إرسال RSVP
do_action('wi_after_rsvp_submit', $guest_id, $response);

// عند إنشاء مناسبة جديدة
do_action('wi_event_created', $event_id, $event_data);
```

#### Filters
```php
// تخصيص محتوى الدعوة
$content = apply_filters('wi_invitation_content', $default_content, $guest_data);

// تخصيص رسالة RSVP
$message = apply_filters('wi_rsvp_success_message', $default_message, $guest_id);

// تخصيص بيانات المدعو
$guest_data = apply_filters('wi_guest_data', $guest_data, $guest_id);
```

### إضافة حقول مخصصة
```php
// إضافة حقل مخصص لجدول المدعوين
function add_custom_guest_field() {
    global $wpdb;
    $table = $wpdb->prefix . 'wi_guests';
    $wpdb->query("ALTER TABLE $table ADD COLUMN custom_field VARCHAR(255)");
}
add_action('wi_plugin_update', 'add_custom_guest_field');
```

## 🌍 دعم اللغات

الإضافة تدعم الترجمة الكاملة ومتاحة باللغات التالية:
- العربية (الافتراضية)
- الإنجليزية

### إضافة ترجمة جديدة
1. انسخ ملف `languages/wedding-invitations.pot`
2. ترجم النصوص باستخدام برنامج مثل Poedit
3. احفظ الملفات بصيغة `.mo` و `.po`
4. ضعها في مجلد `languages/`

## 📊 قاعدة البيانات

### جداول قاعدة البيانات

#### wi_events (المناسبات)
- `id` - معرف المناسبة
- `title` - عنوان المناسبة  
- `description` - وصف المناسبة
- `event_date` - تاريخ المناسبة
- `venue` - مكان المناسبة
- `created_by` - منشئ المناسبة
- `status` - حالة المناسبة

#### wi_guests (المدعوين)
- `id` - معرف المدعو
- `event_id` - معرف المناسبة
- `name` - اسم المدعو
- `email` - البريد الإلكتروني
- `phone` - رقم الهاتف
- `invitation_code` - كود الدعوة الفريد
- `rsvp_status` - حالة الرد
- `plus_one` - هل يمكن إحضار مرافق

#### wi_rsvp_responses (ردود RSVP)
- `id` - معرف الرد
- `guest_id` - معرف المدعو
- `response` - الرد (سيحضر/لن يحضر)
- `message` - رسالة من المدعو
- `dietary_requirements` - متطلبات غذائية

## 🔒 الأمان

### إجراءات الحماية المطبقة
- فحص Nonce في جميع النماذج
- تنظيف وتعقيم البيانات المدخلة
- استخدام Prepared Statements لقاعدة البيانات
- فحص صلاحيات المستخدم
- منع الوصول المباشر للملفات

### أفضل الممارسات الأمنية
```php
// تعقيم البيانات
$clean_input = sanitize_text_field($_POST['input']);

// فحص Nonce
wp_verify_nonce($_POST['nonce'], 'wi_action_nonce');

// فحص الصلاحيات
if (!current_user_can('manage_options')) {
    wp_die(__('غير مسموح', WEDDING_INVITATIONS_TEXT_DOMAIN));
}
```

## 📞 الدعم

### الحصول على المساعدة
- 📧 البريد الإلكتروني: support@example.com
- 🌐 الموقع: https://example.com/support
- 📚 الوثائق: https://example.com/docs

### الإبلاغ عن المشاكل
إذا واجهت أي مشكلة، يرجى تقديم المعلومات التالية:
- إصدار WordPress
- إصدار الإضافة
- وصف تفصيلي للمشكلة
- خطوات إعادة إنتاج المشكلة
- لقطات الشاشة (إن أمكن)

## 📄 الترخيص

هذه الإضافة مرخصة تحت رخصة GPL v2 أو أحدث.

```
Copyright (C) 2024 فريق التطوير

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

## 🤝 المساهمة

نرحب بمساهماتكم في تطوير الإضافة:

### كيفية المساهمة
1. Fork المشروع
2. إنشاء branch للميزة الجديدة (`git checkout -b feature/amazing-feature`)
3. Commit التغييرات (`git commit -m 'Add amazing feature'`)
4. Push إلى Branch (`git push origin feature/amazing-feature`)
5. فتح Pull Request

### معايير الكود
- اتباع WordPress Coding Standards
- كتابة تعليقات باللغة العربية
- إجراء اختبارات للكود الجديد
- توثيق أي تغييرات في API

## 📝 سجل التغييرات

### الإصدار 1.0.0 (2024-09-16)
- 🎉 الإطلاق الأولي للإضافة
- ✨ نظام إدارة المناسبات والمدعوين
- 🎨 واجهة مستخدم حديثة ومتجاوبة  
- 🌍 دعم كامل للغة العربية
- 📱 تصميم متجاوب للأجهزة المحمولة
- 🔒 إجراءات أمان شاملة
- 📊 نظام تقارير مفصل

## 🔮 خارطة الطريق

### الإصدارات القادمة
- 📧 إرسال الدعوات عبر البريد الإلكتروني
- 📱 إشعارات SMS للمدعوين
- 🎨 قوالب دعوات متنوعة
- 📊 تحليلات وإحصائيات متقدمة
- 🗓️ تكامل مع تقاويم Google/Outlook
- 🎵 إضافة خلفيات موسيقية للدعوات
- 🖼️ معرض صور للمناسبة
- 💰 نظام إدارة الهدايا والتبرعات

---

**شكراً لاستخدامكم إضافة إدارة دعوات الزفاف! 💝**
