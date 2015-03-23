define(['app'], function(App){

  return App.module('Ticket.List', function(List, App, Backbone, Marionette, $, _){

    List.Controller = function(query){

      require([
        'Ticket/models/ticket',
        'Ticket/views/list'], function(){

        var request;
        if(query){
          request = App.request('ticket:collection:search', query);
        } else {
          request = App.request('ticket:collection');
        }

        request.done(function(ticketCollection){
          var emptyListView, ticketListView;
          if(ticketCollection.length === 0){
            emptyListView = new List.EmptyView();
            App.mainRegion.show(emptyListView);
            return;
          }

          ticketListView = new List.PaginatedView({
            collection: ticketCollection
          });

          ticketListView.mainView.on('childview:ticket:view', function(childView, ticketModel){
            App.trigger('ticket:view', ticketModel.get('id'));
          });

          ticketListView.mainView.on('ticket:sort', function(sortKey, order){
            ticketCollection.setSorting(sortKey, order);
            ticketCollection.fetch({
              data : ticketCollection.params,
              success : function(){
                ticketListView.mainView.render();
              }
            });
          });

          ticketListView.on('page:change', function(page){
            ticketCollection.getPage(page, {
              data : ticketCollection.params,
              success : function(){
                ticketListView.mainView.render();
              }
            });
            ticketListView.pagerView.model.set(ticketCollection.state);
          });

          App.mainRegion.show(ticketListView);
        });

      });

    };

  });

});