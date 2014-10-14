define(['app'],function(App){
  App.module('Task', function(Task, App, Backbone, Marionette, $, _){
    Task.Router = Marionette.AppRouter.extend({
      appRoutes: {
        "tasks" : "listTasks",
        "tasks/:id" : "viewTask"
      }
    });

    var API = {
      listTasks: function(){
        require(['modules/Task/controllers/list'], function(){
          App.Task.List.Controller.listTasks();
        });
      },
      viewTask: function(id){
        require(['modules/Task/controllers/view'], function(){
          App.Task.View.Controller.viewTask(id);
        });
      }
    };

    App.on('task:list', function(){
      App.navigate("tasks");
      API.listTasks();
    });

    App.on('task:view', function(id){
      App.navigate("tasks/" + id);
      API.viewTask(id);
    });

    App.addInitializer(function(){
      new Task.Router({
        controller: API
      });
    })

  });

});