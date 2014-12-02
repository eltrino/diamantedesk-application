define(['app','./routers/task'], function(App){

  return App.module('Task', function(Task, App, Backbone, Marionette, $, _){

    Task.startWithParent = false;

    Task.addInitializer(function(){
      if(App.getCurrentRoute() === ""){
        App.trigger('task:list');
      }
    });

    App.on('session:login:success', function(){
      Task.start();
      Backbone.history.loadUrl();
    });

  });

});