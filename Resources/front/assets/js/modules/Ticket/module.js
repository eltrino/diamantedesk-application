define(['app','./routers/ticket'], function(App){

  return App.module('Ticket', function(Ticket, App, Backbone, Marionette, $, _){

    Ticket.startWithParent = false;

    Ticket.addInitializer(function(){
      if(App.getCurrentRoute() === ""){
        App.trigger('ticket:list');
      }
    });

    App.on('session:login:success', function(){
      Ticket.start();
      Backbone.history.loadUrl();
      if(App.getCurrentRoute() === "login"){
        App.trigger('ticket:list');
      }
    });

  });

});