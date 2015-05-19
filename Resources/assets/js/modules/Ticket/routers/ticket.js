define(['app'], function(App){

  return App.module('Ticket', function(Ticket, App, Backbone, Marionette, $, _){

    Ticket.startWithParent = false;

    Ticket.Router = Marionette.AppRouter.extend({
      appRoutes: {
        'tickets(/search/:search)(/page/:page)(/sort/:sort)(/order/:order)' : 'listTickets',
        'tickets/create' : 'createTicket',
        'tickets/:id' : 'viewTicket',
        'tickets/:id/edit' : 'editTicket'
      }
    });

    var API = {
      listTickets: function(search, page, sort, order){
        var params = {
          search: search,
          page: ~~page,
          sort: sort,
          order: order
        };
        App.setTitle();
        require(['Ticket/controllers/list'], function(List){
          List.Controller(params);
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
      editTicket: function(key){
        App.setTitle('Edit Ticket');
        require(['Ticket/controllers/edit'], function(Edit){
          Edit.Controller(key);
        });
      }
    };

    App.on('ticket:list', function(){
      App.debug('info', 'Event "ticket:list" fired');
      App.navigate('tickets');
      API.listTickets();
    });

    App.on('ticket:view', function(id, backUrl){
      App.debug('info', 'Event "ticket:view" fired');
      App.navigate("tickets/" + id);
      API.viewTicket(id, backUrl);
    });

    App.on('ticket:create', function(){
      App.debug('info', 'Event "ticket:create" fired');
      App.navigate('tickets/create');
      API.createTicket();
    });

    App.on('ticket:edit', function(key){
      App.debug('info', 'Event "ticket:edit" fired');
      App.navigate('tickets/'+ key + '/edit');
      API.editTicket(key);
    });

    App.on('ticket:search', function(search){
      App.debug('info', 'Event "ticket:search" fired');
      if(search){
        App.navigate('tickets/search/' + search);
        API.listTickets(search);
      } else {
        App.trigger('ticket:list');
      }
    });

    Ticket.on('start',function(){
      new Ticket.Router({
        controller: API
      });

      Backbone.history.on("route", function(router, route, param){
        if(route === "listTickets"){
          App.Header.on('start', function(){
            App.Header.trigger('set:search', param[0]);
          });
          App.Header.trigger('set:search', param[0]);
        } else {
          App.Header.trigger('set:search', null);
        }
      });

    });

  });

});