define(['app'],function(App){
  App.module('Task', function(Task, App, Backbone, Marionette, $, _){
    Task.Router = Marionette.AppRouter.extend({
      appRoutes: {
        "tasks" : "listTasks"
      }
    });

    var API = {
      listTasks: function(){
        require(['modules/Task/controllers/list'], function(){
          App.Task.List.Controller.listTasks();
        });
      }
    };

    App.addInitializer(function(){
      new Task.Router({
        controller: API
      });
    })

  });

});