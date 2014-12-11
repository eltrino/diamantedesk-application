define(['app'], function(App){

  return App.module('Ticket.List', function(List, App, Backbone, Marionette, $, _){

    List.TicketController = function(){

      require(['modules/Ticket/models/ticket', 'modules/Ticket/views/list'], function(){

        var request = App.request("ticket:collection");

        request.done(function(TicketCollection){
          var TicketListView = new List.CompositeView({
            collection: TicketCollection
          });

          TicketListView.on("childview:ticket:view", function(childView, ticketModel){
            App.trigger("ticket:view", ticketModel.get('id'));
          });

          App.MainRegion.show(TicketListView);
        });

      });

    };

  });

});