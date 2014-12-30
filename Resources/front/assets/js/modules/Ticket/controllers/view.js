define(['app'], function(App){

  return App.module('Ticket.View', function(View, App, Backbone, Marionette, $, _){

    View.Controller = function(id){

      require(['Ticket/models/ticket', 'Ticket/views/view'], function(){

        App.request("ticket:model", id).done(function(TicketModel){

          var TicketView = new View.ItemView({
              model : TicketModel
          });
          TicketView.on('show', function(){
            require(['Comment'], function(Comment){
              Comment.start({
                ticket : this.model,
                parentRegion : this.CommentsRegion
              });
            }.bind(this));
          });
          TicketView.on('destroy', function(){
            require(['Comment'], function(Comment){
              Comment.stop();
            });
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