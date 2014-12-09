define(['app'], function(App){

  return App.module("Ticket.Attr", function(Attr, App, Backbone, Marionette, $, _){

    Attr.PriorityModel = Backbone.Model.extend({
      parse : function(attr){
        return { priority : attr.value_to_label_map[attr.priority] };
      }
    });

  });
});