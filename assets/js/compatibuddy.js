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
                plugins: ["search", "themes", "types", "sort"],
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
                                var childNode = $duplicateFiltersTree.jstree(true).get_node(c_v);
                                if (childNode.text.startsWith('Function to Add')) {
                                    functionNodeA = childNode;
                                }
                            });

                            var functionNodeB = null;
                            $.each(nodeB.children, function(c_i, c_v) {
                                var childNode = $duplicateFiltersTree.jstree(true).get_node(c_v);
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
                                var childNode = $duplicateFiltersTree.jstree(true).get_node(c_v);
                                if (childNode.text.startsWith('Priority')) {
                                    priorityNodeA = childNode;
                                }
                            });

                            var priorityNodeB = null;
                            $.each(nodeB.children, function(c_i, c_v) {
                                var childNode = $duplicateFiltersTree.jstree(true).get_node(c_v);
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
                                var childNode = $duplicateFiltersTree.jstree(true).get_node(c_v);
                                if (childNode.text.startsWith('File')) {
                                    fileNodeA = childNode;
                                }
                            });

                            var fileNodeB = null;
                            $.each(nodeB.children, function(c_i, c_v) {
                                var childNode = $duplicateFiltersTree.jstree(true).get_node(c_v);
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
                $duplicateFiltersTree.jstree(true).hide_all();
                return;
            }

            if (includePlugins && includeThemes) {
                $duplicateFiltersTree.jstree(true).show_all();
                return;
            }

            if (!includePlugins) {
                $($duplicateFiltersTree.jstree(true).get_json($duplicateFiltersTree, {
                    flat: true
                }))
                    .each(function (index, value) {
                        var node = $duplicateFiltersTree.jstree(true).get_node(this.id);
                        var lvl = node.parents.length;
                        if (lvl === 2 && node.text.startsWith('Plugin')) {
                            $duplicateFiltersTree.jstree(true).hide_node(node, true);
                            var parent = $duplicateFiltersTree.jstree(true).get_node($duplicateFiltersTree.jstree(true).get_parent(node));
                            var allHidden = true;
                            $.each(parent.children, function(c_i, c_v) {
                                var childNode = $duplicateFiltersTree.jstree(true).get_node(c_v);
                                if (!$duplicateFiltersTree.jstree(true).is_hidden(childNode)) {
                                    allHidden = false;
                                }
                            });

                            if (allHidden) {
                                $duplicateFiltersTree.jstree(true).hide_node(parent, true);
                            }
                        }
                    });
            } else {
                $($duplicateFiltersTree.jstree(true).get_json($duplicateFiltersTree, {
                    flat: true
                }))
                    .each(function (index, value) {
                        var node = $duplicateFiltersTree.jstree(true).get_node(this.id);
                        var lvl = node.parents.length;
                        if (lvl === 2 && node.text.startsWith('Plugin')) {
                            $duplicateFiltersTree.jstree(true).show_node(node, true);
                            var parent = $duplicateFiltersTree.jstree(true).get_node($duplicateFiltersTree.jstree(true).get_parent(node));
                            $duplicateFiltersTree.jstree(true).show_node(parent, true);
                        }
                    });
            }

            $duplicateFiltersTree.jstree(true).redraw();

        });

        $filtersIncludeThemes.on('change', function() {
            var includePlugins = $filtersIncludePlugins.is(':checked');
            var includeThemes = $filtersIncludeThemes.is(':checked');

            if (!includePlugins && !includeThemes) {
                $duplicateFiltersTree.jstree(true).hide_all();
                return;
            }

            if (includePlugins && includeThemes) {
                $duplicateFiltersTree.jstree(true).show_all();
                return;
            }

            if (!includeThemes) {
                $($duplicateFiltersTree.jstree(true).get_json($duplicateFiltersTree, {
                    flat: true
                }))
                    .each(function (index, value) {
                        var node = $duplicateFiltersTree.jstree(true).get_node(this.id);
                        var lvl = node.parents.length;
                        if (lvl === 2 && node.text.startsWith('Theme')) {
                            $duplicateFiltersTree.jstree(true).hide_node(node, true);
                            var parent = $duplicateFiltersTree.jstree(true).get_node($duplicateFiltersTree.jstree(true).get_parent(node));
                            var allHidden = true;
                            $.each(parent.children, function(c_i, c_v) {
                                var childNode = $duplicateFiltersTree.jstree(true).get_node(c_v);
                                if (!$duplicateFiltersTree.jstree(true).is_hidden(childNode)) {
                                    allHidden = false;
                                }
                            });

                            if (allHidden) {
                                $duplicateFiltersTree.jstree(true).hide_node(parent, true);
                            }
                        }
                    });
            } else {
                $($duplicateFiltersTree.jstree(true).get_json($duplicateFiltersTree, {
                    flat: true
                }))
                    .each(function (index, value) {
                        var node = $duplicateFiltersTree.jstree(true).get_node(this.id);
                        var lvl = node.parents.length;
                        if (lvl === 2 && node.text.startsWith('Theme')) {
                            $duplicateFiltersTree.jstree(true).show_node(node, true);
                            var parent = $duplicateFiltersTree.jstree(true).get_node($duplicateFiltersTree.jstree(true).get_parent(node));
                            $duplicateFiltersTree.jstree(true).show_node(parent, true);
                        }
                    });
            }

            $duplicateFiltersTree.jstree(true).redraw();
        });

        $('#compatibuddy-filters-sort').on('submit', function() {
            var sortBy = $('#compatibuddy-filters-sort-by').val();

            switch (sortBy) {
                case 'tag':
                    var node = $duplicateFiltersTree.jstree(true).get_node('#');
                    $duplicateFiltersTree.jstree(true).sort(node, true);
                    $duplicateFiltersTree.jstree(true).redraw_node(node, true);
                    break;
                case 'module-type':
                    $($duplicateFiltersTree.jstree(true).get_json($duplicateFiltersTree, {
                        flat: true
                    }))
                        .each(function (index, value) {
                            var node = $duplicateFiltersTree.jstree(true).get_node(this.id);
                            var lvl = node.parents.length;
                            if (lvl === 1) {
                                $duplicateFiltersTree.jstree(true).sort(node, true);
                                $duplicateFiltersTree.jstree(true).redraw_node(node, true);
                            }
                        });
                    break;
                case 'module-name':
                    $($duplicateFiltersTree.jstree(true).get_json($duplicateFiltersTree, {
                        flat: true
                    }))
                        .each(function (index, value) {
                            var node = $duplicateFiltersTree.jstree(true).get_node(this.id);
                            var lvl = node.parents.length;
                            if (lvl === 1) {
                                $duplicateFiltersTree.jstree(true).sort(node, true);
                                $duplicateFiltersTree.jstree(true).redraw_node(node, true);
                            }
                        });
                    break;
                case 'function-to-add':
                    $($duplicateFiltersTree.jstree(true).get_json($duplicateFiltersTree, {
                        flat: true
                    }))
                        .each(function (index, value) {
                            var node = $duplicateFiltersTree.jstree(true).get_node(this.id);
                            var lvl = node.parents.length;
                            if (lvl === 1) {
                                $duplicateFiltersTree.jstree(true).sort(node, true);
                                $duplicateFiltersTree.jstree(true).redraw_node(node, true);
                            }
                        });
                    break;
                case 'priority':
                    $($duplicateFiltersTree.jstree(true).get_json($duplicateFiltersTree, {
                        flat: true
                    }))
                        .each(function (index, value) {
                            var node = $duplicateFiltersTree.jstree(true).get_node(this.id);
                            var lvl = node.parents.length;
                            if (lvl === 1) {
                                $duplicateFiltersTree.jstree(true).sort(node, true);
                                $duplicateFiltersTree.jstree(true).redraw_node(node, true);
                            }
                        });
                    break;
                case 'file':
                    $($duplicateFiltersTree.jstree(true).get_json($duplicateFiltersTree, {
                        flat: true
                    }))
                        .each(function (index, value) {
                            var node = $duplicateFiltersTree.jstree(true).get_node(this.id);
                            var lvl = node.parents.length;
                            if (lvl === 1) {
                                $duplicateFiltersTree.jstree(true).sort(node, true);
                                $duplicateFiltersTree.jstree(true).redraw_node(node, true);
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