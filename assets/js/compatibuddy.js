var Compatibuddy = (function($) {
    var me = {};
    me.getRandomColors = function(length) {
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

    me.compileFilterPriorityDatasets = function(calls) {
        var datasets = [];
        var moduleColors = [];
        for (var index = 0; index < calls.length; ++index) {
            var priority = parseInt(calls[index]["priority"]);

            if (!(calls[index]["module"]["id"] in moduleColors)) {
                moduleColors[calls[index]["module"]["id"]] = Compatibuddy.getRandomColors(1);
            }

            if (isNaN(priority)) {
                priority = 10;
            }

            datasets.push({
                label: calls[index]["module"]["metadata"]["Name"],
                backgroundColor: moduleColors[calls[index]["module"]["id"]],
                borderColor: "#ffffff",
                data: [priority]
            });
        }

        datasets = datasets.sort(function (a, b) {
            return a.data[0] - b.data[0];
        });

        return datasets;
    };

    me.compileModuleDatasets = function(calls) {
        var datasets = [];
        var tagColors = [];
        for (var tag in calls) {
            if (!calls.hasOwnProperty(tag)) {
                continue;
            }

            tagColors[tag] = Compatibuddy.getRandomColors(1);

            var moduleCalls = [];
            for (var index = 0; index < calls[tag].length; ++index) {
                var priority = parseInt(calls[tag][index]["priority"]);
                if (isNaN(priority)) {
                    priority = 10;
                }

                moduleCalls.push({
                    module: calls[tag][index]["module"]["metadata"]["Name"],
                    priority: priority
                });
            }

            moduleCalls = moduleCalls.sort(function (a, b) {
                return a.priority - b.priority;
            });

            for (var i = 0; i < moduleCalls.length; ++i) {
                datasets.push({
                    label: tag,
                    backgroundColor: tagColors[tag],
                    borderColor: "#ffffff",
                    data: [moduleCalls[i].priority]
                });
            }
        }

        return datasets;
    };

    me.createChart = function(canvasId, type, datasets, yAxesLabel, xAxesLabel) {
        var ctx = document.getElementById(canvasId).getContext("2d");
        var chart = new Chart(ctx, {
            // The type of chart we want to create
            type: type,

            // The data for our dataset
            data: {
                //labels: labels,
                datasets: datasets
            },

            // Configuration options go here
            options: {
                scales: {
                    yAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: yAxesLabel
                        },
                        ticks: {
                            suggestedMin: 0,    // minimum will be 0, unless there is a lower value.
                            // OR //
                            beginAtZero: true   // minimum value will be 0.
                        }
                    }],
                    xAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: xAxesLabel
                        }
                    }]
                }
            }
        });
    };

    return me;
})(jQuery);