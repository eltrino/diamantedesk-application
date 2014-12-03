define(['app'], function(App){

  return App.module('Ticket', function(Ticket, App, Backbone, Marionette, $, _){

    Ticket.startWithParent = false;

    Ticket.Router = Marionette.AppRouter.extend({
      appRoutes: {
        "tickets" : "listTickets",
        "tickets/create" : "createTicket",
        "tickets/:id" : "viewTicket",
        "tickets/:id/edit" : "editTicket"
      }
    });

    var API = {
      listTickets: function(){
        require(['modules/Ticket/controllers/list'], function(List){
          List.TicketController();
        });
      },
      viewTicket: function(id){
        require(['modules/Ticket/controllers/view'], function(View){
          View.TicketController(id);
        });
      },
      createTicket: function(){
        require(['modules/Ticket/controllers/create'], function(Create){
          Create.TicketController();
        });
      },
      editTicket: function(id){
        require(['modules/Ticket/controllers/edit'], function(Edit){
          Edit.TicketController(id);
        });
      }
    };

    App.on('ticket:list', function(){
      App.navigate("tickets");
      API.listTickets();
    });

    App.on('ticket:view', function(id){
      App.navigate("tickets/" + id);
      API.viewTicket(id);
    });

    App.on('ticket:create', function(){
      App.navigate("tickets/create");
      API.createTicket();
    });

    App.on('ticket:edit', function(id){
      App.navigate("tickets/"+ id + "/edit");
      API.editTicket(id);
    });

    Ticket.addInitializer(function(){
      new Ticket.Router({
        controller: API
      });
    });

  });

});