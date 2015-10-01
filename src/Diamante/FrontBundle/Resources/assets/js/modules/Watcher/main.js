define(['app'], function(App){

  return App.module('Ticket.View.Watcher', function(Watcher, App, Backbone, Marionette, $, _){

    Watcher.startWithParent = false;

    Watcher.on('start', function(options){
      Watcher.render(options);
      Watcher.ready = true;
    });

    Watcher.render = function(options){
      require(['Watcher/controllers/watcher'], function(Watcher){
        Watcher.Controller(options);
      });
    };

  });

});