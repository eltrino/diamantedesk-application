define(['app'], function(App){

  return App.module("Ticket.Attr", function(Attr, App, Backbone, Marionette, $, _){

    Attr.StatusModel = Backbone.Model.extend({
      defaults : {
        status: 'new'
      },
      parse : function(attr){
        return { status : attr.value_to_label_map[attr.status] };
      }
    });

  });
});