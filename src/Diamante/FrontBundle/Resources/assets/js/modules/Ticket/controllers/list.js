define(['app', 'Common/views/loader'], function(App, loaderView){

  return App.module('Ticket.List', function(List, App, Backbone, Marionette, $, _){

    List.Controller = function(params){

      App.mainRegion.showLoader();

      require([
        'Ticket/models/ticket',
        'Ticket/views/list'], function(){

        var request = App.request('ticket:collection', params),
            search = params.search;

        if(search){
          App.setTitle(__('diamante_front.ticket.controller.search_result'));
        }

        request.done(function(ticketCollection){
          var emptyListView  = search ? List.EmptySearchView : List.EmptyView,
              ticketListView = new List.PaginatedView({
                isSearch: !!search,
                emptyView: emptyListView,
                collection: ticketCollection
              });

          ticketListView.mainView.on('childview:ticket:view', function(childView, ticketModel){
            ticketListView.mainView.showLoader();
            App.trigger('ticket:view', ticketModel.get('key'), ticketCollection.getParams());
          });

          ticketListView.mainView.on('ticket:sort', function(sortKey, order){
            ticketListView.mainView.showLoader();
            ticketCollection.setSorting(sortKey, order);
            App.navigate('tickets' + ticketCollection.getParams());
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
            App.navigate('tickets' + ticketCollection.getParams());
            ticketListView.pagerView.model.set(ticketCollection.state);
          });

          App.mainRegion.show(ticketListView);
        });

      });

    };

  });

});