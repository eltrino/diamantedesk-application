define(['oroui/js/app/components/base/component' ,'d3', 'd3-tip', 'diamante/palette', 'underscore'], function (BaseComponent, d3, d3tip, palette, _) {

  "use strict";

  var RATIO = 16 / 9,
      resizeGroupedLine = {},
      dateFormat = d3.time.format("%Y-%m-%d"),
      parseDate = dateFormat.parse,
      template = _.template(
          '<div class="tooltip-arrow"></div>' +
          '<div class="tooltip-inner">' +
            'Date: <span><%= date %></span>' +
            '<ul>' +
              '<% _.each(states, function(state){ %>' +
              '<li>' +
                  '<span class="color-label" style="background:<%= state.color %>"></span>' +
                  '<%= state.name %>: <%= state.value %>' +
              '</li>' +
              '<% }) %>' +
            '</ul>' +
          '</div>'
      ),
      sortByDateAscending = function(a, b) { return a.date - b.date;},
      getRandomInt = function(min, max) {
        return Math.floor(Math.random() * (max - min + 1) + min).toString();
      },
      randomData = function () {
        var obj = {};
        var index = -2;
        var last = getRandomInt(3,8);
        var now = new Date();
        var data = function(){
          return { item : getRandomInt(0,5), item2 : getRandomInt(0,5), item3 : getRandomInt(0,5)};
        };
        while(index++, index < last){
          obj[now.getFullYear() + '-' + now.getMonth() + '-' + (now.getDate()+index)] = data();
        }
        return obj
      },
      populateData = function(data){
        var index = 0,
            current = new Date(data[0].date),
            last = new Date(data[data.length - 1].date);
        current.setDate(current.getDate() - 1 );
        last.setDate(last.getDate() + 1 );
        data.splice(0, 0, { date : new Date(current) });
        data.push({ date : new Date(last) });
        while(index++, current < last) {
          current.setDate(current.getDate() + 1);
          if(data[index] && data[index].date > current){
            data.splice(index,0, { date : new Date(current) });
          }
        }

      };

  window.addEventListener('resize', _.debounce(function(){
    for(var key in resizeGroupedLine) {
      if(resizeGroupedLine.hasOwnProperty(key)){
        resizeGroupedLine[key]();
      }
    }
  }, 100), false);

  return function (options) {

    var data = options.data,
        elem = options._sourceElement.get(0),
        parent = options._sourceElement.parent(),
        plot = d3.select(elem),
        isEmpty = (function(){
          if(data.length == 0) {
            return true;
          } else {
            return !_.some(data, function (elem) {
              return _.some(elem, function (value) { return value; });
            });
          }
        })();
    if (isEmpty) {

      $(elem).css({
          opacity: '.2',
          pointerEvents: 'none',
          backgroundColor: '#f2f2f7'
      });

      parent.prepend('<div class="empty-report">No Data. There are no tickets available for analytics yet.</div>');

      data = randomData();
      $('path.line', elem).css('stroke', 'rgba(100,100,100,.7)');

    }

    data = _.map(data, function(value, key){ value.date = parseDate(key); return value;})
        .sort(sortByDateAscending);

    if (!parent.is('[data-wid]')) {
        parent = parent.parent();
    }

    var w = elem.clientWidth,
        h = w / RATIO,
        h2 = 100,
        margin = {top: 20, right: 40, bottom: 30, left: 40},
        width = w - margin.left - margin.right,
        height = h - margin.top - margin.bottom - (h2 + margin.top);

    if(parent[0].id == 'container' && h > parent[0].clientHeight - h2){
      h = parent[0].clientHeight - h2;
      height = h - margin.top - margin.bottom - (h2 + margin.top);
    }

    var svg = plot.append("svg")
        .attr("width", w)
        .attr("height", h)
        .attr("viewBox", "0 0 " + w + " " + h);

    var focus = svg.append("g")
        .attr("class", "focus")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

    var context = svg.append("g")
        .attr("class", "context")
        .attr("transform", "translate(" + margin.left + "," + (margin.top * 2 + height) + ")");

    svg.append("defs").append("clipPath")
        .attr("id", "clip")
        .append("rect")
        .attr("width", width)
        .attr("height", height + 1);

    var x = d3.time.scale().range([0, width]),
        x2 = d3.time.scale().range([0, width]),
        y = d3.scale.linear().range([height, 0]),
        y2 = d3.scale.linear().range([h2, 0]);

    var keys = _.chain(data)
        .map(function(elem){ return d3.keys(elem)})
        .flatten()
        .uniq()
        .filter(function(key) { return key !== "date"; })
        .value();

    var paletteLength = Object.keys(palette).length;
    var dataLength = (data.length <= paletteLength) ? data.length : paletteLength;

    var color = d3.scale.ordinal().domain(keys).range(palette[dataLength]);

    populateData(data);

    var tickets = color.domain().map(function(name) {
      return {
        name: name,
        values: data.map(function(d) {
          return {date: d.date, state: d[name] ? +d[name] : 0};
        })
      };
    });

    var ticksCount = parseInt(
          d3.max(tickets, function(c) { return d3.max(c.values, function(v) { return v.state; }); }) -
          d3.min(tickets, function(c) { return d3.min(c.values, function(v) { return v.state; }); })
        ,10) + 1;
    if(ticksCount > 20) {
      ticksCount = 20;
    }

    var xAxis = d3.svg.axis().scale(x).orient("bottom"),
        xAxis2 = d3.svg.axis().scale(x2).orient("bottom"),
        yAxis = d3.svg.axis().scale(y).orient("left").ticks(ticksCount);

    var brushed = function() {
      x.domain(brush.empty() ? x2.domain() : brush.extent());
      focus.select(".x.axis").call(xAxis);
      focus.selectAll('.line')
          .attr("d", function(d) { return line(d.values); });
      focus.selectAll('.tooltip-holder')
          .attr("transform", function(d){ return "translate(" + (x(d.date) - 10) + ",0)"})
    };

    var brush = d3.svg.brush()
        .x(x2)
        .on("brush", brushed);

    var line = d3.svg.line()
        .interpolate("linear")
        .x(function(d) { return x(d.date); })
        .y(function(d) { return y(d.state); });

    var line2 = d3.svg.line()
        .interpolate("linear")
        .x(function(d) { return x2(d.date); })
        .y(function(d) { return y2(d.state); });

    var tip = d3tip()
        .attr('class', 'diam-d3-tip tooltip bottom')
        .direction('s')
        .offset([20, 0])
        .html(function(d) {
          var _data = {
            date : dateFormat(d.date),
            states : _.map(keys, function(key){
                      return {
                        name : key[0].toUpperCase() + key.slice(1),
                        value : d[key]? d[key] : 0,
                        color: color(key)
                      }
                    })
          };
          return template(_data);
        });

    focus.call(tip);

    x.domain(d3.extent(data, function(d) { return d.date; }));

    y.domain([
      d3.min(tickets, function(c) { return d3.min(c.values, function(v) { return v.state; }); }),
      d3.max(tickets, function(c) { return d3.max(c.values, function(v) { return v.state + 1; }); })
    ]);

    x2.domain(x.domain());
    y2.domain(y.domain());

    focus.append("g")
        .attr("class", "x axis")
        .attr("transform", "translate(0," + height + ")")
        .call(xAxis);

    context.append("g")
        .attr("class", "x axis")
        .attr("transform", "translate(0," + h2 + ")")
        .call(xAxis2);

    focus.append("g")
        .attr("class", "y axis")
        .call(yAxis);
        //.append("text")
        //.attr("transform", "rotate(-90)")
        //.attr("y", 6)
        //.attr("dy", ".71em")
        //.style("text-anchor", "end")
        //.text("Tickets State");

    var ticket = focus.selectAll(".ticket")
        .data(tickets)
        .enter().append("g")
        .attr("class", "ticket")
        .style("clip-path", "url('#clip')");

    var ticket2 = context.selectAll(".ticket")
        .data(tickets)
        .enter().append("g")
        .attr("class", "ticket");

    ticket.append("path")
        .attr("class", "line")
        .attr("d", function(d) { return line(d.values); })
        .style("stroke", function(d) { return color(d.name); });

    ticket2.append("path")
        .attr("class", "line")
        .attr("d", function(d) { return line2(d.values); })
        .style("stroke", function(d) { return color(d.name); });


    focus.selectAll('.tooltip-holder')
        .data(data).enter()
        .append('g')
        .attr('class', 'tooltip-holder')
        .attr("transform", function(d){ return "translate(" + (x(d.date) - 10) + ",0)";})
        .append("rect")
        .attr('width', 20)
        .attr('height', height)
        .on('mouseover', tip.show)
        .on('mouseout', tip.hide);

    focus.selectAll('.tooltip-holder')
        .append('line')
        .attr('y2', height)
        .attr('x1', 10)
        .attr('x2', 10);

    context.append("g")
        .attr("class", "x brush")
        .call(brush)
        .selectAll("rect")
        .attr("y", -6)
        .attr("height", h2 + 7);

    var legend = _.map(keys, function(key){
      return {
        key: key,
        color: color(key)
      };
    });

    var legendBox = focus.selectAll('.legend')
        .data([true]).enter()
        .append('g')
        .attr('class', 'legend');

    var legendBlock = legendBox.selectAll('.legend-block')
        .data([true]).enter()
        .append('rect')
        .attr('class', 'legend-block');

    var legendItem = legendBox.selectAll('.legend-item')
        .data([true]).enter()
        .append('g')
        .attr('class', 'legend-item');

    legendItem.selectAll("text")
        .data(legend, function(d) { return d.key})
        .call(function(d) { d.enter().append("text")})
        .call(function(d) { d.exit().remove()})
        .attr("y",function(d,i) { return i + 0.1+"em"})
        .attr("x","1em")
        .text(function(d) { return d.key[0].toUpperCase() + d.key.slice(1); });

    legendItem.selectAll("circle")
        .data(legend, function(d) { return d.key})
        .call(function(d) { d.enter().append("circle")})
        .call(function(d) { d.exit().remove()})
        .attr("cy",function(d,i) { return i-0.25+"em"})
        .attr("cx",0)
        .attr("r","0.4em")
        .style("fill",function(d) { return d.color; });

    var bbox = legendItem[0][0].getBBox(),
        padding = 8;
    legendBlock.attr("x",(bbox.x-padding))
        .attr("y",(bbox.y-padding))
        .attr("height",(bbox.height+2*padding))
        .attr("width",(bbox.width+2*padding));


    legendBox
        .attr("transform", function(){ return "translate("+ (width - legendBlock.attr('width')) +", 30)"});

    resizeGroupedLine[parent[0].id] = function () {
      var w = elem.clientWidth,
          h = w / RATIO,
          width = w - margin.left - margin.right,
          height = h - margin.top - margin.bottom - (h2 + margin.top);
      if(w <= 0) {
        delete resizeGroupedLine[parent[0].id];
        return;
      }
      if(parent[0].id == 'container' && h > parent[0].clientHeight - h2){
        h = parent[0].clientHeight - h2;
        height = h - margin.top - margin.bottom - (h2 + margin.top);
      }

      x.range([0, width]);
      x2.range([0, width]);
      y.range([height, 0]);

      xAxis.scale(x);
      xAxis2.scale(x2);
      yAxis.scale(y);

      svg
          .attr("width", w)
          .attr("height", h)
          .attr("viewBox", "0 0 " + w + " " + h);

      svg.select("#clip").select("rect")
          .attr("width", width)
          .attr("height", height + 1);

      context.attr("transform", "translate(" + margin.left + "," + (margin.top * 2 + height) + ")");

      focus.select('.x.axis')
          .attr("transform", "translate(0," + height + ")")
          .call(xAxis);

      focus.select('.y.axis')
          .call(yAxis);

      focus.selectAll('.tooltip-holder')
          .attr("transform", function(d){ return "translate(" + (x(d.date) - 10) + ",0)"})
          .select('rect')
          .attr('height', height);

      focus.selectAll('.tooltip-holder')
          .select('line')
          .attr('y2', height);

      context.select('.x.axis')
          .call(xAxis2);

      legendBox
          .attr("transform", function(){ return "translate("+ (width - legendBlock.attr('width')) +", 30)"});

      focus.selectAll('.line').attr("d", function(d) { return line(d.values); });
      context.selectAll('.line').attr("d", function(d) { return line2(d.values); });
    };

    if ( isEmpty ) {
      $('path.line', elem).css('stroke', 'rgba(100,100,100,.7)');
      //$('g.context', elem).css('display', 'none');
      $('g.legend', elem).css('display', 'none');
    }

  };

});
