<div class="wrap">
    <h1><?php _e('ماسح رمز QR', 'wedding-invitations'); ?></h1>
    <p><?php _e('استخدم الكاميرا لمسح رمز QR الخاص بالضيف عند مدخل المناسبة للتحقق من الدعوة وتسجيل الحضور.', 'wedding-invitations'); ?></p>

    <div id="wi-qr-reader" style="width: 500px; max-width: 100%;"></div>
    <div id="wi-qr-reader-result" style="margin-top: 20px; font-size: 18px; font-weight: bold;"></div>
</div>

<!-- Include the QR Code scanning library -->
<script src="https://unpkg.com/html5-qrcode/html5-qrcode.min.js"></script>

<script type="text/javascript">
    jQuery(document).ready(function($) {

        const resultContainer = document.getElementById('wi-qr-reader-result');
        let lastScanTime = 0;
        const scanCooldown = 3000; // 3 seconds cooldown

        function onScanSuccess(decodedText, decodedResult) {
            const currentTime = new Date().getTime();
            if (currentTime - lastScanTime < scanCooldown) {
                // Still in cooldown, ignore scan
                return;
            }
            lastScanTime = currentTime;

            // The decoded text is the validation URL, e.g., https://site.com/validate-guest?guest_code=HASH
            // We need to extract the hash.
            let guestCode = '';
            try {
                const url = new URL(decodedText);
                guestCode = url.searchParams.get("guest_code");
            } catch (e) {
                // Could not parse URL, maybe the QR code is not a URL.
                // We can assume the decodedText is the hash itself for robustness.
                guestCode = decodedText;
            }

            if (!guestCode) {
                resultContainer.innerHTML = '<span style="color: red;">رمز QR غير صالح.</span>';
                return;
            }

            // Set loading state
            resultContainer.innerHTML = '<span style="color: orange;">جاري التحقق...</span>';

            // Now, send this code to the backend for validation via AJAX
            $.ajax({
                url: ajaxurl, // WordPress provides this global variable in admin
                type: 'POST',
                data: {
                    action: 'wi_validate_qr_code',
                    nonce: '<?php echo wp_create_nonce("wi_qr_scanner_nonce"); ?>',
                    qr_code_hash: guestCode
                },
                success: function(response) {
                    if (response.success) {
                        resultContainer.innerHTML = '<span style="color: green;">' + response.data.message + '</span>';
                    } else {
                        resultContainer.innerHTML = '<span style="color: red;">' + response.data.message + '</span>';
                    }
                },
                error: function() {
                    resultContainer.innerHTML = '<span style="color: red;">حدث خطأ في الشبكة.</span>';
                }
            });
        }

        var html5QrcodeScanner = new Html5QrcodeScanner(
            "wi-qr-reader",
            { fps: 10, qrbox: 250 },
            /* verbose= */ false
        );
        html5QrcodeScanner.render(onScanSuccess);

    });
</script>
