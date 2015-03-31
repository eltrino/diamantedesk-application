define([
  'app',
  'Common/views/pagination',
  'tpl!../templates/list-item.ejs',
  'tpl!../templates/list.ejs',
  'tpl!../templates/empty-search.ejs',
  'tpl!../templates/empty-list.ejs'], function(App,Pagination, listItemTemplate, listTemplate, emptySearchTemplate, emptyListTemplate){

  return App.module('Ticket.List', function(List, App, Backbone, Marionette, $, _){

    List.ItemView = Marionette.ItemView.extend({
      tagName: 'tr',
      template: listItemTemplate,

      templateHelpers: function(){
        return {
          created: new Date(this.model.get('created_at')).toLocaleDateString(),
          status: this.model.get('status').replace(/_/g,' ')
        };
      },

      events: {
        'click': 'viewClicked'
      },

      viewClicked: function(e){
        e.preventDefault();
        this.trigger('ticket:view', this.model);
      }
    });

    List.EmptyView = Marionette.ItemView.extend({
      template: emptyListTemplate,
      tagName: 'tr',
      className: 'message danger'
    });

    List.EmptySearchView = Marionette.ItemView.extend({
      template: emptySearchTemplate,
      tagName: 'tr',
      className: 'message danger'
    });

    List.CompositeView = Marionette.CompositeView.extend({
      tagName: 'table',
      template: listTemplate,
      className: 'ticket-list table table-hover table-bordered',
      childViewContainer: 'tbody',
      childView: List.ItemView,

      events: {
        'click .sortable': 'sortHandle'
      },

      sortHandle: function(e){
        var sortKey = e.target.className.replace(' sortable',''),
            order = -1;
        if(this.collection.state.sortKey == sortKey) {
          order = this.collection.state.order > 0 ? -1 : 1;
        }
        this.trigger('ticket:sort', sortKey, order);
      },

      templateHelpers: function(){
        var filterState = this.collection.state;
        return {
          sorterState: function(attr){
            if(filterState.sortKey === attr) {
              if(filterState.order > 0){
                return '<i class="fa fa-sort-desc"></i>';
              } else {
                return '<i class="fa fa-sort-asc"></i>';
              }
            } else {
              return '<i class="fa fa-sort"></i>';
            }
          }
        };
      }

    });

    List.PaginatedView = Pagination.LayoutView.extend({
      MainView: List.CompositeView
    });

  });

});