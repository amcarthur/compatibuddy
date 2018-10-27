var Compatibuddy = (function($) {
    var me = {};
    me._getRandomColors = function(length) {
        var colorMap = {
            aqua: "#00ffff",
            azure: "#f0ffff",
            beige: "#f5f5dc",
            black: "#000000",
            blue: "#0000ff",
            brown: "#a52a2a",
            cyan: "#00ffff",
            darkblue: "#00008b",
            darkcyan: "#008b8b",
            darkgrey: "#a9a9a9",
            darkgreen: "#006400",
            darkkhaki: "#bdb76b",
            darkmagenta: "#8b008b",
            darkolivegreen: "#556b2f",
            darkorange: "#ff8c00",
            darkorchid: "#9932cc",
            darkred: "#8b0000",
            darksalmon: "#e9967a",
            darkviolet: "#9400d3",
            fuchsia: "#ff00ff",
            gold: "#ffd700",
            green: "#008000",
            indigo: "#4b0082",
            khaki: "#f0e68c",
            lightblue: "#add8e6",
            lightcyan: "#e0ffff",
            lightgreen: "#90ee90",
            lightgrey: "#d3d3d3",
            lightpink: "#ffb6c1",
            lightyellow: "#ffffe0",
            lime: "#00ff00",
            magenta: "#ff00ff",
            maroon: "#800000",
            navy: "#000080",
            olive: "#808000",
            orange: "#ffa500",
            pink: "#ffc0cb",
            purple: "#800080",
            violet: "#800080",
            red: "#ff0000",
            silver: "#c0c0c0",
            yellow: "#ffff00"
        };

        var colors = [];
        while (colors.length < length) {
            var result;
            var count = 0;
            for (var prop in colorMap) {
                if (Math.random() < 1/++count) {
                    result = prop;
                }
            }

            if (length === 1) {
                return result;
            }

            if(colors.indexOf(result) > -1) {
                continue;
            }
            colors.push(result);
        }
        return colors;
    };

    me._compileFilterPriorityData= function(calls) {
        var self = this;
        var data = {
            labels: [],
            datasets: []
        };

        var keyedDatasets = [];
        var moduleCalls = [];
        for (var index = 0; index < calls.length; ++index) {
            if (!(calls[index]["module"]["id"] in moduleCalls)) {
                moduleCalls[calls[index]["module"]["id"]] = [];
                data.labels.push(calls[index]["module"]["metadata"]["Name"]);
            }
            moduleCalls[calls[index]["module"]["id"]].push(calls[index]);
        }

        for (var id in moduleCalls) {
            if (!moduleCalls.hasOwnProperty(id)) {
                continue;
            }

            for (var i = 0; i < moduleCalls[id].length; ++i) {
                var priority = parseInt(moduleCalls[id][i]["priority"]);
                if (isNaN(priority)) {
                    priority = 10;
                }

                if (!(id in keyedDatasets)) {
                    keyedDatasets[id] = {
                        backgroundColor: self._getRandomColors(1),
                        data: [],
                        datalabels: {
                            align: 'center',
                            anchor: 'center'
                        }
                    };
                }

                keyedDatasets[id].data.push(priority);
            }
        }

        for (var k in keyedDatasets) {
            if (!keyedDatasets.hasOwnProperty(k)) {
                continue;
            }

            data.datasets.push(keyedDatasets[k]);
        }

        return data;
    };

    me.createFilterPrioritiesChart = function(canvasId, type, calls, yAxesLabel, xAxesLabel) {
        var self = this;
        var ctx = document.getElementById(canvasId).getContext("2d");

        Chart.helpers.merge(Chart.defaults.global, {
            aspectRatio: 4/3,
            tooltips: false,
            layout: {
                padding: {
                    top: 42,
                    right: 16,
                    bottom: 32,
                    left: 8
                }
            },
            elements: {
                line: {
                    fill: false
                },
                point: {
                    hoverRadius: 7,
                    radius: 5
                }
            },
            plugins: {
                legend: false,
                title: false
            }
        });

        var chart = new Chart(ctx, {
            type: type,
            data: self._compileFilterPriorityData(calls),
            options: {
                plugins: {
                    datalabels: {
                        color: 'white',
                        display: function(context) {
                            return true;
                        },
                        font: {
                            weight: 'bold'
                        },
                        formatter: Math.round
                    }
                },
                scales: {
                    yAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: yAxesLabel
                        },
                        ticks: {
                            suggestedMin: 0,
                            beginAtZero: true
                        }
                    }],
                    xAxes: [{
                        type: 'category',
                        scaleLabel: {
                            display: true,
                            labelString: xAxesLabel
                        }
                    }]
                }
            }
        });
    };

    me.createFunctionCallTree = function(selector) {
        var $treeContainer = $(selector);
        if ($treeContainer.length === 0) {
            return;
        }

        var $tree = $treeContainer.find('.compatibuddy-tree');
        var $sortForm = $treeContainer.find('form.compatibuddy-tree-sort');
        var $sortBy = $sortForm.find('select[name="compatibuddy-tree-sort-by"]');
        var $searchForm = $treeContainer.find('form.compatibuddy-tree-search');
        var $searchQuery = $searchForm.find('input[name="compatibuddy-tree-search-query"]');
        var $includePlugins = $treeContainer.find('input[type="checkbox"].compatibuddy-tree-include-plugins');
        var $includeThemes = $treeContainer.find('input[type="checkbox"].compatibuddy-tree-include-themes');
        var $loading = $treeContainer.find('.compatibuddy-tree-loading');

        $tree
            .on('ready.jstree', function(e) {
                $tree.addClass('ready');
                $loading.addClass('ready');
            })
            .on('state_ready.jstree', function() {
                $tree.on('select_node.jstree', function(e,data) {
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
                    var sortBy = $sortBy.val();
                    var orderBy = $sortForm.find('input[name="compatibuddy-tree-sort-order"]:checked').val();
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
                            if (nodeA.parents.length !== 3) {
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
                            if (nodeA.parents.length !== 3) {
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
                            if (nodeA.parents.length !== 3) {
                                break;
                            }

                            var functionNodeA = null;
                            $.each(nodeA.children, function(c_i, c_v) {
                                var childNode = $tree.jstree(true).get_node(c_v);
                                if (childNode.text.startsWith('Function to Add')) {
                                    functionNodeA = childNode;
                                }
                            });

                            var functionNodeB = null;
                            $.each(nodeB.children, function(c_i, c_v) {
                                var childNode = $tree.jstree(true).get_node(c_v);
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
                            if (nodeA.parents.length !== 3) {
                                break;
                            }

                            var priorityNodeA = null;
                            $.each(nodeA.children, function(c_i, c_v) {
                                var childNode = $tree.jstree(true).get_node(c_v);
                                if (childNode.text.startsWith('Priority')) {
                                    priorityNodeA = childNode;
                                }
                            });

                            var priorityNodeB = null;
                            $.each(nodeB.children, function(c_i, c_v) {
                                var childNode = $tree.jstree(true).get_node(c_v);
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
                            if (nodeA.parents.length !== 3) {
                                break;
                            }

                            var fileNodeA = null;
                            $.each(nodeA.children, function(c_i, c_v) {
                                var childNode = $tree.jstree(true).get_node(c_v);
                                if (childNode.text.startsWith('File')) {
                                    fileNodeA = childNode;
                                }
                            });

                            var fileNodeB = null;
                            $.each(nodeB.children, function(c_i, c_v) {
                                var childNode = $tree.jstree(true).get_node(c_v);
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

        $searchForm.on('submit', function(e) {
            var query = $searchQuery.val().trim();
            if (query.length > 0) {
                $tree.jstree(true).search(query);
            } else {
                $tree.jstree(true).clear_search();
            }

            return false;
        });

        $includePlugins.on('change', function() {
            var includePlugins = $includePlugins.is(':checked');
            var includeThemes = $includeThemes.is(':checked');

            if (!includePlugins && !includeThemes) {
                $tree.jstree(true).hide_all();
                return;
            }

            if (includePlugins && includeThemes) {
                $tree.jstree(true).show_all();
                return;
            }

            if (!includePlugins) {
                $($tree.jstree(true).get_json($tree, {
                    flat: true
                }))
                    .each(function (index, value) {
                        var node = $tree.jstree(true).get_node(this.id);
                        var lvl = node.parents.length;
                        if (lvl === 3 && node.text.startsWith('Plugin')) {
                            $tree.jstree(true).hide_node(node, true);
                            var parent = $tree.jstree(true).get_node($tree.jstree(true).get_parent(node));
                            var root = $tree.jstree(true).get_node($tree.jstree(true).get_parent(parent));
                            var allHidden = true;
                            $.each(parent.children, function(c_i, c_v) {
                                var childNode = $tree.jstree(true).get_node(c_v);
                                if (!$tree.jstree(true).is_hidden(childNode)) {
                                    allHidden = false;
                                }
                            });

                            if (allHidden) {
                                $tree.jstree(true).hide_node(root, true);
                            }
                        }
                    });
            } else {
                $($tree.jstree(true).get_json($tree, {
                    flat: true
                }))
                    .each(function (index, value) {
                        var node = $tree.jstree(true).get_node(this.id);
                        var lvl = node.parents.length;
                        if (lvl === 3 && node.text.startsWith('Plugin')) {
                            $tree.jstree(true).show_node(node, true);
                            var parent = $tree.jstree(true).get_node($tree.jstree(true).get_parent(node));
                            var root = $tree.jstree(true).get_node($tree.jstree(true).get_parent(parent));
                            $tree.jstree(true).show_node(root, true);
                        }
                    });
            }

            $tree.jstree(true).redraw();

        });

        $includeThemes.on('change', function() {
            var includePlugins = $includePlugins.is(':checked');
            var includeThemes = $includeThemes.is(':checked');

            if (!includePlugins && !includeThemes) {
                $tree.jstree(true).hide_all();
                return;
            }

            if (includePlugins && includeThemes) {
                $tree.jstree(true).show_all();
                return;
            }

            if (!includeThemes) {
                $($tree.jstree(true).get_json($tree, {
                    flat: true
                }))
                    .each(function (index, value) {
                        var node = $tree.jstree(true).get_node(this.id);
                        var lvl = node.parents.length;
                        if (lvl === 3 && node.text.startsWith('Theme')) {
                            $tree.jstree(true).hide_node(node, true);
                            var parent = $tree.jstree(true).get_node($tree.jstree(true).get_parent(node));
                            var root = $tree.jstree(true).get_node($tree.jstree(true).get_parent(parent));
                            var allHidden = true;
                            $.each(parent.children, function(c_i, c_v) {
                                var childNode = $tree.jstree(true).get_node(c_v);
                                if (!$tree.jstree(true).is_hidden(childNode)) {
                                    allHidden = false;
                                }
                            });

                            if (allHidden) {
                                $tree.jstree(true).hide_node(root, true);
                            }
                        }
                    });
            } else {
                $($tree.jstree(true).get_json($tree, {
                    flat: true
                }))
                    .each(function (index, value) {
                        var node = $tree.jstree(true).get_node(this.id);
                        var lvl = node.parents.length;
                        if (lvl === 3 && node.text.startsWith('Theme')) {
                            $tree.jstree(true).show_node(node, true);
                            var parent = $tree.jstree(true).get_node($tree.jstree(true).get_parent(node));
                            var root = $tree.jstree(true).get_node($tree.jstree(true).get_parent(parent));
                            $tree.jstree(true).show_node(root, true);
                        }
                    });
            }

            $tree.jstree(true).redraw();
        });

        $sortForm.on('submit', function() {
            var sortBy = $sortBy.val();

            switch (sortBy) {
                case 'tag':
                    var node = $tree.jstree(true).get_node('#');
                    $tree.jstree(true).sort(node, true);
                    $tree.jstree(true).redraw_node(node, true);
                    break;
                case 'module-type':
                    $($tree.jstree(true).get_json($tree, {
                        flat: true
                    }))
                        .each(function (index, value) {
                            var node = $tree.jstree(true).get_node(this.id);
                            var lvl = node.parents.length;
                            if (lvl === 1) {
                                $tree.jstree(true).sort(node, true);
                                $tree.jstree(true).redraw_node(node, true);
                            }
                        });
                    break;
                case 'module-name':
                    $($tree.jstree(true).get_json($tree, {
                        flat: true
                    }))
                        .each(function (index, value) {
                            var node = $tree.jstree(true).get_node(this.id);
                            var lvl = node.parents.length;
                            if (lvl === 1) {
                                $tree.jstree(true).sort(node, true);
                                $tree.jstree(true).redraw_node(node, true);
                            }
                        });
                    break;
                case 'function-to-add':
                    $($tree.jstree(true).get_json($tree, {
                        flat: true
                    }))
                        .each(function (index, value) {
                            var node = $tree.jstree(true).get_node(this.id);
                            var lvl = node.parents.length;
                            if (lvl === 2) {
                                $tree.jstree(true).sort(node, true);
                                $tree.jstree(true).redraw_node(node, true);
                            }
                        });
                    break;
                case 'priority':
                    $($tree.jstree(true).get_json($tree, {
                        flat: true
                    }))
                        .each(function (index, value) {
                            var node = $tree.jstree(true).get_node(this.id);
                            var lvl = node.parents.length;
                            if (lvl === 2) {
                                $tree.jstree(true).sort(node, true);
                                $tree.jstree(true).redraw_node(node, true);
                            }
                        });
                    break;
                case 'file':
                    $($tree.jstree(true).get_json($tree, {
                        flat: true
                    }))
                        .each(function (index, value) {
                            var node = $tree.jstree(true).get_node(this.id);
                            var lvl = node.parents.length;
                            if (lvl === 2) {
                                $tree.jstree(true).sort(node, true);
                                $tree.jstree(true).redraw_node(node, true);
                            }
                        });
                    break;
                default:
                    break;
            }

            return false;
        });
    };

    $(document).on('ready', function() {
        me.createFunctionCallTree('#compatibuddy-filters-tree-container');
        $('#compatibuddy-filter-import-button').on('click', function(e) {
            $('#compatibuddy-filter-import-upload').toggle();
            return false;
        });


    });

    return me;
})(jQuery);