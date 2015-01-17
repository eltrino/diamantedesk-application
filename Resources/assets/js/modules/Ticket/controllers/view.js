define(['app'], function(App){

  return App.module('Ticket.View', function(View, App, Backbone, Marionette, $, _){

    View.Controller = function(id){

      require(['Ticket/models/ticket', 'Ticket/views/view'], function(){

        App.request('ticket:model', id).done(function(ticketModel){

          var ticketView = new View.ItemView({
              model : ticketModel
          });
          ticketView.on('show', function(){
            require(['Comment'], function(Comment){
              Comment.start({
                ticket : this.model,
                parentRegion : this.CommentsRegion
              });
            }.bind(this));
          });
          ticketView.on('destroy', function(){
            require(['Comment'], function(Comment){
              Comment.stop();
            });
          });
          App.mainRegion.show(ticketView);

        }).fail(function(){

          var missingView = new View.MissingView();
          App.mainRegion.show(missingView);

        });

      });

    };

  });

});