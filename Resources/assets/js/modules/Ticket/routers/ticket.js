define(['app'], function(App){

  return App.module('Ticket', function(Ticket, App, Backbone, Marionette, $, _){

    Ticket.startWithParent = false;

    Ticket.Router = Marionette.AppRouter.extend({
      appRoutes: {
        'tickets' : 'listTickets',
        'tickets/create' : 'createTicket',
        'tickets/:id' : 'viewTicket',
        'tickets/:id/edit' : 'editTicket',
        'tickets/search/:query' : 'searchTicket'
      }
    });

    var API = {
      listTickets: function(){
        App.setTitle();
        require(['Ticket/controllers/list'], function(List){
          List.Controller();
        });
      },
      viewTicket: function(id, query){
        App.setTitle('View Ticket');
        require(['Ticket/controllers/view'], function(View){
          View.Controller(id, query);
        });
      },
      createTicket: function(){
        App.setTitle('Create Ticket');
        require(['Ticket/controllers/create'], function(Create){
          Create.Controller();
        });
      },
      editTicket: function(id){
        App.setTitle('Edit Ticket');
        require(['Ticket/controllers/edit'], function(Edit){
          Edit.Controller(id);
        });
      },
      searchTicket: function(query){
        App.setTitle('Search Result');
        require(['Ticket/controllers/list'], function(List){
          List.Controller(query);
        });
      }
    };

    App.on('ticket:list', function(){
      App.debug('info', 'Event "ticket:list" fired');
      App.navigate('tickets');
      API.listTickets();
    });

    App.on('ticket:view', function(id, query){
      App.debug('info', 'Event "ticket:view" fired');
      App.navigate("tickets/" + id);
      API.viewTicket(id, query);
    });

    App.on('ticket:create', function(){
      App.debug('info', 'Event "ticket:create" fired');
      App.navigate('tickets/create');
      API.createTicket();
    });

    App.on('ticket:edit', function(id){
      App.debug('info', 'Event "ticket:edit" fired');
      App.navigate('tickets/'+ id + '/edit');
      API.editTicket(id);
    });

    App.on('ticket:search', function(query){
      App.debug('info', 'Event "ticket:search" fired');
      if(query){
        App.navigate('tickets/search/' + query);
        API.searchTicket(query);
      } else {
        App.trigger('ticket:list');
      }
    });

    Ticket.on('start',function(){
      new Ticket.Router({
        controller: API
      });

      Backbone.history.on("route", function(router, route, param){
        if(route === "searchTicket"){
          App.Header.trigger('set:search', param[0]);
        } else {
          App.Header.trigger('set:search', null);
        }
      });

    });

  });

});