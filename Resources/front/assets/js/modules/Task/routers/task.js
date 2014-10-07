define(['app'],function(App){
  App.module('Task', function(Task, App, Backbone, Marionette, $, _){
    Task.Router = Marionette.AppRouter.extend({
      appRoutes: {
        "tasks" : "listTasks",
        "tasks/:id" : "showTask"
      }
    });

    var API = {
      listTasks: function(){
        require(['modules/Task/controllers/list'], function(){
          App.Task.List.Controller.listTasks();
        });
      },
      showTask: function(id){
        require(['modules/Task/controllers/show'], function(){
          App.Task.Show.Controller.showTask(id);
        });
      }
    };

    App.on('task:list', function(){
      App.navigate("tasks");
      API.listTasks();
    });

    App.on('task:show', function(id){
      App.navigate("tasks/" + id);
      API.showTask(id);
    });

    App.addInitializer(function(){
      new Task.Router({
        controller: API
      });
    })

  });

});