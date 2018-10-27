(function($) {
    $(document).ready(function() {
        $('.compatibuddy-scan-plugin-link').on('click', function(e) {
            e.preventDefault();

            var data = {
                'action': 'compatibuddy_scan_plugin',
                '_wpnonce': ajax_object.ajax_nonce,
                'plugin': $(this).data('plugin')
            };

            $.post(ajax_object.ajax_url, data, function(response) {
                location.reload();
            });
        });

        $('.compatibuddy-scan-theme-link').on('click', function(e) {
            e.preventDefault();

            var data = {
                'action': 'compatibuddy_scan_theme',
                '_wpnonce': ajax_object.ajax_nonce,
                'theme': $(this).data('theme')
            };

            $.post(ajax_object.ajax_url, data, function(response) {
                location.reload();
            });
        });
    });
})(jQuery);