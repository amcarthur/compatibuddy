(function($) {
    $(document).ready(function() {
        $('.compatibuddy-scan-link').on('click', function(e) {
            e.preventDefault();

            var data = {
                'action': 'compatibuddy_scan',
                '_wpnonce': ajax_object.ajax_nonce,
                'plugin': $(this).data('plugin')
            };

            $.post(ajax_object.ajax_url, data, function(response) {
                location.reload();
            });
        });
    });
})(jQuery);