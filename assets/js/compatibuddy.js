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

        console.log("unkeyed: ", data);

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

    return me;
})(jQuery);