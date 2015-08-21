define(['d3', 'd3-tip', 'underscore'], function (d3, d3tip, _) {

  "use strict";

  var RATIO = 16/ 9,
      resizePie = {},
      toInt = function(num){ return parseInt(num, 10)},
      toPercent = function(num, max){ return num/max * 100 >= 1 ? Math.round(num/max * 100) : "< 1";},
      midAngle = function (data){ return data.startAngle + (data.endAngle - data.startAngle)/2; };

  window.addEventListener('resize', _.debounce(function(){
    for(var key in resizePie) {
      if(resizePie.hasOwnProperty(key)){
        resizePie[key]();
      }
    }
  }, 100), false);

  return function (options) {

    var data = options.data,
        parent = options.parent.el,
        elem = options._sourceElement.get(0),
        plot = d3.select(elem),
        sum = _.reduce(data, function(memo, elem){ return memo + toInt(elem.data); }, 0);


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
        .attr("transform", "translate(" + w/2 + "," + h/2  + ")");

    root.append("g")
        .attr("class", "slices");
    root.append("g")
        .attr("class", "labels");
    root.append("g")
        .attr("class", "lines");

    var pie = d3.layout.pie()
        .sort(null)
        .value(function(d) {
          return d.data;
        });

    var arc = d3.svg.arc()
        .outerRadius(radius * 0.8)
        .innerRadius(radius * 0.4);

    var outerArc = d3.svg.arc()
        .innerRadius(radius * 0.9)
        .outerRadius(radius * 0.9);

    var color = d3.scale.category20();


    var slice = svg.select(".slices").selectAll("path.slice")
        .data(pie(data));

    slice.enter()
        .insert("path")
        .style("fill", function(d) { return color(d.data.label); })
        .attr("class", "slice")
        .attr("d", arc);

    slice.exit()
        .remove();

    var text = root.select(".labels").selectAll("text")
        .data(pie(data));

    text.enter()
        .append("text")
        .attr("dy", ".35em")
        .style("text-anchor", function(d){
          return midAngle(d) < Math.PI ? "start":"end"
        })
        .attr("transform", function(d) {
          var pos = outerArc.centroid(d);
          pos[0] = radius * (midAngle(d) < Math.PI ? 1 : -1);
          return "translate(" + pos + ")";
        })
        .text(function(d) {
          return d.data.label + ' ' + toPercent(d.data.data, sum) + "%" ;
        });

    text.exit()
        .remove();

    var polyline = svg.select(".lines").selectAll("polyline")
        .data(pie(data));

    polyline.enter()
        .append("polyline")
        .attr("points", function(d){
          var pos = outerArc.centroid(d);
          pos[0] = radius * 0.95 * (midAngle(d) < Math.PI ? 1 : -1);
          return [arc.centroid(d), outerArc.centroid(d), pos];
        });

    polyline.exit()
        .remove();

    resizePie[parent.id] = function () {
      var w = elem.clientWidth,
          h = w / RATIO,
          width = w - margin.left - margin.right,
          height = h - margin.top - margin.bottom,
          radius = Math.min(width, height) / 2;


      arc.outerRadius(radius * 0.8).innerRadius(radius * 0.4);
      outerArc.outerRadius(radius * 0.9).innerRadius(radius * 0.9);

      svg.attr("viewBox", "0 0 " + w + " " + h);
      root.attr("transform", "translate(" + w/2 + "," + h/2  + ")");

      svg.selectAll('.slice').attr("d", arc);

      text.attr("transform", function(d) {
        var pos = outerArc.centroid(d);
        pos[0] = radius * (midAngle(d) < Math.PI ? 1 : -1);
        return "translate(" + pos + ")";
      });

      polyline.attr("points", function(d){
        var pos = outerArc.centroid(d);
        pos[0] = radius * 0.95 * (midAngle(d) < Math.PI ? 1 : -1);
        return [arc.centroid(d), outerArc.centroid(d), pos];
      });

    };

  };

});