define(['d3', 'd3-tip', 'diamante/palette', 'underscore'], function (d3, d3tip, palette, _) {

    "use strict";

    var RATIO = 16 / 9,
        resizePie = {},
        toInt = function (num) {
            return parseInt(num, 10)
        },
        toPercent = function (num, max) {
            return num / max * 100 >= 1 ? Math.round(num / max * 100) : "< 1";
        },
        midAngle = function (data) {
            return data.startAngle + (data.endAngle - data.startAngle) / 2;
        };

    window.addEventListener('resize', _.debounce(function () {
        for (var key in resizePie) {
            if (resizePie.hasOwnProperty(key)) {
                resizePie[key]();
            }
        }
    }, 100), false);

    return function (options) {

        var data = options.data,
            elem = options._sourceElement.get(0),
            parent = options._sourceElement.parent(),
            plot = d3.select(elem),
            sum = _.reduce(data, function (memo, elem) {
                return memo + toInt(elem.data);
            }, 0),
            isEmpty = !data.length,
            getRandomInt = function (min, max) {
                return Math.floor(Math.random() * (max - min + 1) + min).toString();
            };

        if (isEmpty) {
            parent.prepend('<div class="empty-widget">No Data. There are no tickets available for analytics yet.</div>');
            $(elem).css({
                opacity: '.2',
                pointerEvents: 'none',
                backgroundColor: '#f2f2f7'
            });

            data = [
                {
                    data: getRandomInt(10, 0),
                    label: "Item1"
                },
                {
                    data: getRandomInt(10, 0),
                    label: "Item2"
                },
                {
                    data: getRandomInt(10, 0),
                    label: "Item3"
                }
            ];
            sum = _.reduce(data, function (memo, elem) {
                return memo + toInt(elem.data);
            }, 0);
        }

        if (!parent.is('[data-wid]')) {
            parent = parent.parent();
        }

        var w = elem.clientWidth,
            h = w / RATIO,
            margin = {top: 20, right: 20, bottom: 30, left: 40},
            width = w - margin.left - margin.right,
            height = h - margin.top - margin.bottom,
            radius = Math.min(width, height) / 2;

        var svg = plot.append("svg")
            .attr("width", w)
            .attr("height", h)
            .attr("viewBox", "0 0 " + w + " " + h);

        var root = svg.append("g")
            .attr("transform", "translate(" + w / 2 + "," + h / 2 + ")");

        root.append("g")
            .attr("class", "slices");
        root.append("g")
            .attr("class", "labels");
        root.append("g")
            .attr("class", "lines");

        var pie = d3.layout.pie()
            .sort(null)
            .value(function (d) {
                return d.data;
            });

        var arc = d3.svg.arc()
            .outerRadius(radius * 0.8)
            .innerRadius(radius * 0.4);

        var outerArc = d3.svg.arc()
            .innerRadius(radius * 0.9)
            .outerRadius(radius * 0.9);

        var color = d3.scale.ordinal().domain(data).range(palette[data.length]);

        var slice = svg.select(".slices").selectAll("path.slice")
            .data(pie(data));

        slice.enter()
            .insert("path")
            .style("fill", function (d) {
                return color(d.data.label);
            })
            .attr("class", "slice")
            .attr("d", arc);

        if (isEmpty) {
            $('path.slice', elem).css('fill', '#646464');
            $('path.slice:nth-child(2)', elem).css('opacity', '.7');
            $('path.slice:nth-child(3)', elem).css('opacity', '.4');
        }

        slice.exit()
            .remove();

        var text = root.select(".labels").selectAll("text")
            .data(pie(data));



        text.enter()
            .append("text")
            .attr("dy", ".35em")
            .style("text-anchor", function (d) {
                return midAngle(d) < Math.PI ? "start" : "end"
            })
            .attr("transform", function (d) {
                var pos = outerArc.centroid(d);
                pos[0] = radius * (midAngle(d) < Math.PI ? 1 : -1);

                return "translate(" + pos + ")";
            })
            .text(function (d) {
                return (!isEmpty) ? (d.data.label + ' ' + toPercent(d.data.data, sum) + "%") : '';
            }).each(function (d) {
                textWrap.call(this, d);
        });

        text.exit()
            .remove();

        var polyline = svg.select(".lines").selectAll("polyline")
            .data(pie(data));

        polyline.enter()
            .append("polyline")
            .attr("points", function (d) {
                var pos = outerArc.centroid(d);
                pos[0] = radius * 0.95 * (midAngle(d) < Math.PI ? 1 : -1);
                return (!isEmpty) ? ([arc.centroid(d), outerArc.centroid(d), pos]) : '';
            });

        polyline.exit()
            .remove();

        resizePie[parent[0].id] = function () {
            var w = elem.clientWidth,
                h = w / RATIO,
                width = w - margin.left - margin.right,
                height = h - margin.top - margin.bottom,
                radius = Math.min(width, height) / 2;

            if (w <= 0) {
                delete resizePie[parent[0].id];
                return;
            }


            arc.outerRadius(radius * 0.8).innerRadius(radius * 0.4);
            outerArc.outerRadius(radius * 0.9).innerRadius(radius * 0.9);

            svg.attr("viewBox", "0 0 " + w + " " + h);
            root.attr("transform", "translate(" + w / 2 + "," + h / 2 + ")");

            svg.selectAll('.slice').attr("d", arc);

            text.attr("transform", function (d) {
                var pos = outerArc.centroid(d);
                pos[0] = radius * (midAngle(d) < Math.PI ? 1 : -1);
                return "translate(" + pos + ")";
            }).each (function(d) {
                textWrap.call(this, d);
            });

            polyline.attr("points", function (d) {
                var pos = outerArc.centroid(d);
                pos[0] = radius * 0.95 * (midAngle(d) < Math.PI ? 1 : -1);
                return [arc.centroid(d), outerArc.centroid(d), pos];
            });

        };

        function textWrap(d) {
            var w = elem.clientWidth,
                h = w / RATIO,
                width = w - margin.left - margin.right,
                height = h - margin.top - margin.bottom,
                radius = Math.min(width, height) / 2,
                pos = Math.abs(radius * (midAngle(d) < Math.PI ? 1 : -1)),
                visibleTextWidth = w / 2 - pos,
                textWidth = this.getComputedTextLength(),
                text = d3.select(this),
                t = text.html().replace(/<tspan.+?>|<\/tspan>/g, " ").replace(/\s+/g, " ").trim(),
                words = t.split(/\s+/).reverse(),
                line = [],
                lineHeight = 16,
                y = text.attr("y"),
                dy = parseFloat(text.attr("dy")),
                word;

            if (visibleTextWidth < textWidth) {
                var tspan = text.text(null).append("tspan").attr("x", 0).attr("y", y).attr("dy", dy + "em");

                while (word = words.pop()) {
                    line.push(word);
                    tspan.text(line.join(" "));

                    if (tspan.node().getComputedTextLength() > visibleTextWidth) {
                        line.pop();
                        tspan.text(line.join(" "));
                        line = [word];
                        tspan = text.append("tspan").attr("x", 0).attr("y", y).attr("dy", lineHeight);
                        tspan.text(line.join(" "));
                    }
                }
            }
        }

    };

});
