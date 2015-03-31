define(['app', 'Common/views/loader'], function(App, loaderView){

  return App.module('Ticket.List', function(List, App, Backbone, Marionette, $, _){

    List.Controller = function(query){

      App.mainRegion.showLoader();

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
          var emptyListView  = query ? List.EmptySearchView : List.EmptyView,
              ticketListView = new List.PaginatedView({
                emptyView: emptyListView,
                collection: ticketCollection
              });

          ticketListView.mainView.on('childview:ticket:view', function(childView, ticketModel){
            ticketListView.mainView.showLoader();
            App.trigger('ticket:view', ticketModel.get('id'));
          });

          ticketListView.mainView.on('ticket:sort', function(sortKey, order){
            ticketListView.mainView.showLoader();
            ticketCollection.setSorting(sortKey, order);
            ticketCollection.fetch({
              data : ticketCollection.params,
              success : function(){
                ticketListView.mainView.render();
              }
            });
          });

          ticketListView.on('page:change', function(page){
            ticketListView.mainView.showLoader();
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