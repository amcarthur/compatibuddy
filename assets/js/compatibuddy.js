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

        var $duplicateFiltersTree = $('#compatibuddy-duplicate-filters-tree');
        $duplicateFiltersTree
            .on('ready.jstree', function(e) {
                $(this).addClass('ready');
            })
            .bind('select_node.jstree', function(e,data) {
                if (data.node.a_attr.href !== '#') {
                    window.location.href = data.node.a_attr.href;
                }
            })
            .jstree({
                types: {
                    "root": {
                        "icon" : "dashicons dashicons-image-filter"
                    },
                    "plugin": {
                        "icon" : "dashicons dashicons-editor-code"
                    },
                    "theme": {
                        "icon" : "dashicons dashicons-admin-customizer"
                    },
                    "function": {
                        "icon" : "dashicons dashicons-plus"
                    },
                    "priority": {
                        "icon" : "dashicons dashicons-dashboard"
                    },
                    "accepted_args": {
                        "icon" : "dashicons dashicons-exerpt-view"
                    },
                    "file": {
                        "icon" : "dashicons dashicons-format-aside"
                    },
                    "line": {
                        "icon" : "dashicons dashicons-editor-ol"
                    },
                    "edit": {
                        "icon" : "dashicons dashicons-edit"
                    },
                    "default" : {
                    }
                },
                plugins: ["search", "themes", "types"]
            });

        $('#compatibuddy-filters-search').on('submit', function(e) {
            var query = $(this).find(':input[type="text"]').val().trim();
            if (query.length > 0) {
                $duplicateFiltersTree.jstree(true).search(query);
            } else {
                $duplicateFiltersTree.jstree(true).clear_search();
            }

            return false;
        });

        var $higherPriorityFiltersTree = $('#compatibuddy-higher-priority-filters-tree');
        $higherPriorityFiltersTree
            .on('ready.jstree', function(e) {
                $(this).addClass('ready');
            })
            .bind("select_node.jstree", function (e, data) {
                if (data.node.a_attr.href !== '#') {
                    window.location.href = data.node.a_attr.href;
                }
            })
            .jstree();
    });
})(jQuery);