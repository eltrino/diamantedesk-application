define(['app','./routers/ticket'], function(App){

  return App.module('Ticket', function(Ticket, App, Backbone, Marionette, $, _){

    Ticket.startWithParent = false;

    Ticket.addInitializer(function(){
      Backbone.history.loadUrl();
      if(App.getCurrentRoute() === ""){
        App.trigger('ticket:list');
      }
    });

    App.on('session:login:success', function(){
      Ticket.start();
      if(App.getCurrentRoute() === "login"){
        App.trigger('ticket:list');
      }
    });

  });

});