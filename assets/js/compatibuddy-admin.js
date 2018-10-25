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

        var $filtersTree = $('#compatibuddy-duplicate-filters-tree');
        $filtersTree
            .on('ready.jstree', function(e) {
                $(this).addClass('ready');
                $('.compatibuddy-tree-loading').addClass('ready');
            })
            .on('state_ready.jstree', function() {
                $filtersTree.on('select_node.jstree', function(e,data) {
                    if (data.node.a_attr.href !== '#') {
                        window.location.href = data.node.a_attr.href;
                    }
                });
            })
            .jstree({
                plugins: ["search", "themes", "types", "sort", "state"],
                types: {
                    "root": {
                        "icon" : "dashicons dashicons-image-filter"
                    },
                    "function_name": {
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
                sort: function(a, b) {
                    var sortBy = $('#compatibuddy-filters-sort-by').val();
                    var orderBy = $(':input[name="compatibuddy-filters-sort-by-order"]:checked').val();
                    var nodeA = this.get_node(a);
                    var nodeB = this.get_node(b);
                    var valueRegex = /(\<strong\>)(.*)(\<\/strong\>)/;

                    switch (sortBy) {
                        case 'tag':
                            if (nodeA.parents.length !== 1) {
                                break;
                            }

                            var matchesA = valueRegex.exec(nodeA.text);
                            if (matchesA === null || matchesA.length !== 4) {
                                break;
                            }

                            var matchesB = valueRegex.exec(nodeB.text);
                            if (matchesB === null || matchesB.length !== 4) {
                                break;
                            }

                            var valueA = matchesA[2].toLocaleLowerCase();
                            var valueB = matchesB[2].toLocaleLowerCase();

                            if (valueA.charAt(0) === '\'') {
                                valueA = valueA.substr(1);
                            }

                            if (valueB.charAt(0) === '\'') {
                                valueB = valueB.substr(1);
                            }

                            if (valueA.charAt(0) === '"') {
                                valueA = valueA.substr(1);
                            }

                            if (valueB.charAt(0) === '"') {
                                valueB = valueB.substr(1);
                            }

                            return orderBy === 'asc' ? valueA.localeCompare(valueB) : valueA.localeCompare(valueB) * -1;
                        case 'module-type':
                            if (nodeA.parents.length !== 2) {
                                break;
                            }

                            var typeRegex = /(Plugin\:|Theme:)/;
                            var matchesA = typeRegex.exec(nodeA.text);
                            var matchesB = typeRegex.exec(nodeB.text);

                            if (matchesA === null || matchesB === null) {
                                break;
                            }

                            if (matchesA[0] === 'Plugin:' && matchesB[0] === 'Theme:') {
                                return orderBy === 'asc' ? -1 : 1;
                            }

                            if (matchesA[0] === 'Theme:' && matchesB[0] === 'Plugin:') {
                                return orderBy === 'asc' ? 1 : -1;
                            }

                            break;
                        case 'module-name':
                            if (nodeA.parents.length !== 2) {
                                break;
                            }

                            var matchesA = valueRegex.exec(nodeA.text);
                            if (matchesA === null || matchesA.length !== 4) {
                                break;
                            }

                            var matchesB = valueRegex.exec(nodeB.text);
                            if (matchesB === null || matchesB.length !== 4) {
                                break;
                            }

                            var valueA = matchesA[2].toLocaleLowerCase();
                            var valueB = matchesB[2].toLocaleLowerCase();

                            return orderBy === 'asc' ? valueA.localeCompare(valueB) : valueA.localeCompare(valueB) * -1;
                        case 'function-to-add':
                            if (nodeA.parents.length !== 2) {
                                break;
                            }

                            var functionNodeA = null;
                            $.each(nodeA.children, function(c_i, c_v) {
                                var childNode = $filtersTree.jstree(true).get_node(c_v);
                                if (childNode.text.startsWith('Function to Add')) {
                                    functionNodeA = childNode;
                                }
                            });

                            var functionNodeB = null;
                            $.each(nodeB.children, function(c_i, c_v) {
                                var childNode = $filtersTree.jstree(true).get_node(c_v);
                                if (childNode.text.startsWith('Function to Add')) {
                                    functionNodeB = childNode;
                                }
                            });

                            if (functionNodeA === null || functionNodeB === null) {
                                break;
                            }

                            var matchesA = valueRegex.exec(functionNodeA.text);
                            if (matchesA === null || matchesA.length !== 4) {
                                break;
                            }

                            var matchesB = valueRegex.exec(functionNodeB.text);
                            if (matchesB === null || matchesB.length !== 4) {
                                break;
                            }

                            var valueA = matchesA[2].toLocaleLowerCase();
                            var valueB = matchesB[2].toLocaleLowerCase();

                            return orderBy === 'asc' ? valueA.localeCompare(valueB) : valueA.localeCompare(valueB) * -1;
                        case 'priority':
                            if (nodeA.parents.length !== 2) {
                                break;
                            }

                            var priorityNodeA = null;
                            $.each(nodeA.children, function(c_i, c_v) {
                                var childNode = $filtersTree.jstree(true).get_node(c_v);
                                if (childNode.text.startsWith('Priority')) {
                                    priorityNodeA = childNode;
                                }
                            });

                            var priorityNodeB = null;
                            $.each(nodeB.children, function(c_i, c_v) {
                                var childNode = $filtersTree.jstree(true).get_node(c_v);
                                if (childNode.text.startsWith('Priority')) {
                                    priorityNodeB = childNode;
                                }
                            });

                            if (priorityNodeA === null || priorityNodeB === null) {
                                break;
                            }

                            var matchesA = valueRegex.exec(priorityNodeA.text);
                            if (matchesA === null || matchesA.length !== 4) {
                                break;
                            }

                            var matchesB = valueRegex.exec(priorityNodeB.text);
                            if (matchesB === null || matchesB.length !== 4) {
                                break;
                            }

                            var valueA = matchesA[2];
                            var valueB = matchesB[2];

                            if (valueA === 'PHP_INT_MAX') {
                                return -1;
                            }

                            if (valueB === 'PHP_INT_MAX') {
                                return 1;
                            }

                            if (valueA === 'PHP_INT_MIN') {
                                return 1;
                            }

                            if (valueB === 'PHP_INT_MIN') {
                                return -1;
                            }

                            if (valueA === "&lt;N/A&gt;") {
                                valueA = 10;
                            }

                            if (valueB === "&lt;N/A&gt;") {
                                valueB = 10;
                            }

                            return parseInt(valueA) > parseInt(valueB) ? (orderBy === 'asc' ? -1 : 1) : (orderBy === 'asc' ? 1 : -1);
                        case 'file':
                            if (nodeA.parents.length !== 2) {
                                break;
                            }

                            var fileNodeA = null;
                            $.each(nodeA.children, function(c_i, c_v) {
                                var childNode = $filtersTree.jstree(true).get_node(c_v);
                                if (childNode.text.startsWith('File')) {
                                    fileNodeA = childNode;
                                }
                            });

                            var fileNodeB = null;
                            $.each(nodeB.children, function(c_i, c_v) {
                                var childNode = $filtersTree.jstree(true).get_node(c_v);
                                if (childNode.text.startsWith('File')) {
                                    fileNodeB = childNode;
                                }
                            });

                            if (fileNodeA === null || fileNodeB === null) {
                                break;
                            }

                            var matchesA = valueRegex.exec(fileNodeA.text);
                            if (matchesA === null || matchesA.length !== 4) {
                                break;
                            }

                            var matchesB = valueRegex.exec(fileNodeB.text);
                            if (matchesB === null || matchesB.length !== 4) {
                                break;
                            }

                            var valueA = matchesA[2].toLocaleLowerCase();
                            var valueB = matchesB[2].toLocaleLowerCase();

                            return orderBy === 'asc' ? valueA.localeCompare(valueB) : valueA.localeCompare(valueB) * -1;
                        default:
                            break;
                    }
                }
            });

        $('#compatibuddy-filters-search').on('submit', function(e) {
            var query = $(this).find(':input[type="text"]').val().trim();
            if (query.length > 0) {
                $filtersTree.jstree(true).search(query);
            } else {
                $filtersTree.jstree(true).clear_search();
            }

            return false;
        });

        $('#compatibuddy-filter-import-button').on('click', function(e) {
            $('#compatibuddy-filter-import-upload').toggle();
            return false;
        });

        var $filtersIncludePlugins = $('#compatibuddy-filters-include-plugins');
        var $filtersIncludeThemes = $('#compatibuddy-filters-include-themes');

        $filtersIncludePlugins.on('change', function() {
            var includePlugins = $filtersIncludePlugins.is(':checked');
            var includeThemes = $filtersIncludeThemes.is(':checked');

            if (!includePlugins && !includeThemes) {
                $filtersTree.jstree(true).hide_all();
                return;
            }

            if (includePlugins && includeThemes) {
                $filtersTree.jstree(true).show_all();
                return;
            }

            if (!includePlugins) {
                $($filtersTree.jstree(true).get_json($filtersTree, {
                    flat: true
                }))
                    .each(function (index, value) {
                        var node = $filtersTree.jstree(true).get_node(this.id);
                        var lvl = node.parents.length;
                        if (lvl === 2 && node.text.startsWith('Plugin')) {
                            $filtersTree.jstree(true).hide_node(node, true);
                            var parent = $filtersTree.jstree(true).get_node($filtersTree.jstree(true).get_parent(node));
                            var allHidden = true;
                            $.each(parent.children, function(c_i, c_v) {
                                var childNode = $filtersTree.jstree(true).get_node(c_v);
                                if (!$filtersTree.jstree(true).is_hidden(childNode)) {
                                    allHidden = false;
                                }
                            });

                            if (allHidden) {
                                $filtersTree.jstree(true).hide_node(parent, true);
                            }
                        }
                    });
            } else {
                $($filtersTree.jstree(true).get_json($filtersTree, {
                    flat: true
                }))
                    .each(function (index, value) {
                        var node = $filtersTree.jstree(true).get_node(this.id);
                        var lvl = node.parents.length;
                        if (lvl === 2 && node.text.startsWith('Plugin')) {
                            $filtersTree.jstree(true).show_node(node, true);
                            var parent = $filtersTree.jstree(true).get_node($filtersTree.jstree(true).get_parent(node));
                            $filtersTree.jstree(true).show_node(parent, true);
                        }
                    });
            }

            $filtersTree.jstree(true).redraw();

        });

        $filtersIncludeThemes.on('change', function() {
            var includePlugins = $filtersIncludePlugins.is(':checked');
            var includeThemes = $filtersIncludeThemes.is(':checked');

            if (!includePlugins && !includeThemes) {
                $filtersTree.jstree(true).hide_all();
                return;
            }

            if (includePlugins && includeThemes) {
                $filtersTree.jstree(true).show_all();
                return;
            }

            if (!includeThemes) {
                $($filtersTree.jstree(true).get_json($filtersTree, {
                    flat: true
                }))
                    .each(function (index, value) {
                        var node = $filtersTree.jstree(true).get_node(this.id);
                        var lvl = node.parents.length;
                        if (lvl === 2 && node.text.startsWith('Theme')) {
                            $filtersTree.jstree(true).hide_node(node, true);
                            var parent = $filtersTree.jstree(true).get_node($filtersTree.jstree(true).get_parent(node));
                            var allHidden = true;
                            $.each(parent.children, function(c_i, c_v) {
                                var childNode = $filtersTree.jstree(true).get_node(c_v);
                                if (!$filtersTree.jstree(true).is_hidden(childNode)) {
                                    allHidden = false;
                                }
                            });

                            if (allHidden) {
                                $filtersTree.jstree(true).hide_node(parent, true);
                            }
                        }
                    });
            } else {
                $($filtersTree.jstree(true).get_json($filtersTree, {
                    flat: true
                }))
                    .each(function (index, value) {
                        var node = $filtersTree.jstree(true).get_node(this.id);
                        var lvl = node.parents.length;
                        if (lvl === 2 && node.text.startsWith('Theme')) {
                            $filtersTree.jstree(true).show_node(node, true);
                            var parent = $filtersTree.jstree(true).get_node($filtersTree.jstree(true).get_parent(node));
                            $filtersTree.jstree(true).show_node(parent, true);
                        }
                    });
            }

            $filtersTree.jstree(true).redraw();
        });

        $('#compatibuddy-filters-sort').on('submit', function() {
            var sortBy = $('#compatibuddy-filters-sort-by').val();

            switch (sortBy) {
                case 'tag':
                    var node = $filtersTree.jstree(true).get_node('#');
                    $filtersTree.jstree(true).sort(node, true);
                    $filtersTree.jstree(true).redraw_node(node, true);
                    break;
                case 'module-type':
                    $($filtersTree.jstree(true).get_json($filtersTree, {
                        flat: true
                    }))
                        .each(function (index, value) {
                            var node = $filtersTree.jstree(true).get_node(this.id);
                            var lvl = node.parents.length;
                            if (lvl === 1) {
                                $filtersTree.jstree(true).sort(node, true);
                                $filtersTree.jstree(true).redraw_node(node, true);
                            }
                        });
                    break;
                case 'module-name':
                    $($filtersTree.jstree(true).get_json($filtersTree, {
                        flat: true
                    }))
                        .each(function (index, value) {
                            var node = $filtersTree.jstree(true).get_node(this.id);
                            var lvl = node.parents.length;
                            if (lvl === 1) {
                                $filtersTree.jstree(true).sort(node, true);
                                $filtersTree.jstree(true).redraw_node(node, true);
                            }
                        });
                    break;
                case 'function-to-add':
                    $($filtersTree.jstree(true).get_json($filtersTree, {
                        flat: true
                    }))
                        .each(function (index, value) {
                            var node = $filtersTree.jstree(true).get_node(this.id);
                            var lvl = node.parents.length;
                            if (lvl === 1) {
                                $filtersTree.jstree(true).sort(node, true);
                                $filtersTree.jstree(true).redraw_node(node, true);
                            }
                        });
                    break;
                case 'priority':
                    $($filtersTree.jstree(true).get_json($filtersTree, {
                        flat: true
                    }))
                        .each(function (index, value) {
                            var node = $filtersTree.jstree(true).get_node(this.id);
                            var lvl = node.parents.length;
                            if (lvl === 1) {
                                $filtersTree.jstree(true).sort(node, true);
                                $filtersTree.jstree(true).redraw_node(node, true);
                            }
                        });
                    break;
                case 'file':
                    $($filtersTree.jstree(true).get_json($filtersTree, {
                        flat: true
                    }))
                        .each(function (index, value) {
                            var node = $filtersTree.jstree(true).get_node(this.id);
                            var lvl = node.parents.length;
                            if (lvl === 1) {
                                $filtersTree.jstree(true).sort(node, true);
                                $filtersTree.jstree(true).redraw_node(node, true);
                            }
                        });
                    break;
                default:
                    break;
            }

            return false;
        });
    });
})(jQuery);