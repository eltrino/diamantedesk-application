define(['app'], function(App){

  return App.module('Ticket.List', function(List, App, Backbone, Marionette, $, _){

    List.TicketController = function(){

      require(['modules/Ticket/models/Ticket', 'modules/Ticket/views/list'], function(){

        var request = App.request("ticket:collection");

        request.done(function(TicketCollection){
          var TicketListView = new List.CompositeView({
            collection: TicketCollection
          });

          TicketListView.on("childview:ticket:view", function(childView, TicketModel){
            App.trigger("ticket:view", TicketModel.get('id'));
          });

          App.MainRegion.show(TicketListView);
        });

      });

    };

  });

});