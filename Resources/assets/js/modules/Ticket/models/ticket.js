define([
  'app',
  'config',
  'backbone.paginator'], function(App, Config){

  return App.module("Ticket", function(Ticket, App, Backbone, Marionette, $, _){

    Ticket.Model = Backbone.Model.extend({
      urlRoot : Config.apiUrl + '/desk/tickets',
      defaults: {
        subject : '',
        description: '',
        priority : 'medium',
        status: 'open',
        source: 'web'
      }
    });

    Ticket.Collection = Backbone.PageableCollection.extend({
      url: Config.apiUrl + '/desk/tickets',
      model: Ticket.Model,

      state: {
        pageSize: 10,
        sortKey: 'createdAt',
        order: -1,
        totalPages: 2  //WARNING
      },

      queryParams: {
        pageSize: 'perPage',
        sortKey: 'orderByField',
        order: 'sortingOrder'
      },

      setFilter: function(query) {
        this.params = { subject : query };
      }

    });

    var API = {
      getTicketCollection: function(query) {
        var tickets = new Ticket.Collection(),
            defer = $.Deferred();
        if(query){
          tickets.setFilter(query);
        }
        tickets.fetch({
          data : tickets.params,
          success : function(data){
            defer.resolve(data);
          }
        });
        return defer.promise();
      },
      getTicketModel: function(id) {
        var ticket = new Ticket.Model({id:id}),
            defer = $.Deferred();
        ticket.fetch({
          success: function(data){
            defer.resolve(data);
          },
          error: function(){
            defer.reject();
          }
        });
        return defer.promise();
      }
    };

    App.reqres.setHandler("ticket:collection", function(){
      return API.getTicketCollection();
    });

    App.reqres.setHandler("ticket:collection:search", function(query){
      return API.getTicketCollection(query);
    });

    App.reqres.setHandler("ticket:model", function(id){
      return API.getTicketModel(id);
    });

  });

});

