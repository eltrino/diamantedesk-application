define(['app'], function(App){

  return App.module('Ticket.List', function(List, App, Backbone, Marionette, $, _){

    List.Controller = function(query){

      require(['Ticket/models/ticket', 'Ticket/views/list'], function(){

        var request;
        if(query){
          request = App.request('ticket:collection:filter', query);
        } else {
          request = App.request('ticket:collection');
        }

        request.done(function(TicketCollection){
          var TicketListView = new List.CompositeView({
            collection: TicketCollection
          });

          TicketListView.on('childview:ticket:view', function(childView, ticketModel){
            App.trigger('ticket:view', ticketModel.get('id'));
          });

          TicketListView.on('ticket:sort', function(sortKey, order){
            TicketCollection.setSorting(sortKey, order);
            TicketCollection.fetch({
              success : function(){
                TicketListView.render();
              }
            });
          });

          App.MainRegion.show(TicketListView);
        });

      });

    };

  });

});