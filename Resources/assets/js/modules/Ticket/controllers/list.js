define(['app'], function(App){

  return App.module('Ticket.List', function(List, App, Backbone, Marionette, $, _){

    List.Controller = function(query){

      require([
        'Ticket/models/ticket',
        'Ticket/views/list'], function(){

        var request;
        if(query){
          request = App.request('ticket:collection:search', query);
          App.Header.trigger('set:search', query);
        } else {
          request = App.request('ticket:collection');
        }

        request.done(function(ticketCollection){
          var ticketListView = new List.PaginatedView({
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
            ticketCollection.getPage(page);
            ticketCollection.fetch({
              data : ticketCollection.params,
              success : function(){
                ticketListView.pagerView.model.set(ticketCollection.state);
                ticketListView.mainView.render();
              }
            });
          });

          ticketListView.on('destroy', function(){
            App.Header.trigger('set:search', null);
          });

          App.mainRegion.show(ticketListView);
        });

      });

    };

  });

});