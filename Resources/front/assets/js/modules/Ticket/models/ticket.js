define([
  'app',
  'config'], function(App, Config){

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

    Ticket.Collection = Backbone.Collection.extend({
      url: Config.apiUrl+ '/desk/tickets',
      model: Ticket.Model
    });

    var API = {
      getTicketCollection: function() {
        var tickets = new Ticket.Collection(),
            defer = $.Deferred();
        tickets.fetch({
          success: function(data){
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

    App.reqres.setHandler("ticket:model", function(id){
      return API.getTicketModel(id);
    });

  });

});

