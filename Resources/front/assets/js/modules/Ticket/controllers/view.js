define(['app'], function(App){

  return App.module('Ticket.View', function(View, App, Backbone, Marionette, $, _){

    View.TicketController = function(id){

      require(['modules/Ticket/models/ticket', 'modules/Ticket/views/view'], function(){

        App.request("ticket:model", id).done(function(TicketModel){

          var TicketView = new View.ItemView({
            model : TicketModel
          });
          App.MainRegion.show(TicketView);

        }).fail(function(){

          var missingView = new View.MissingView();
          App.MainRegion.show(missingView);

        });

      });

    };

  });

});