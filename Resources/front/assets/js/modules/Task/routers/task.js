define(function(){

  App.module('Task.Routers', function(Routers, App, Backbone, Marionette, $, _){
    Routers = Marionette.AppRouter.extend({
      appRoutes: {
        "tasks" : "listTasks",
        "tasks/create" : "createTask",
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
      },
      createTask: function(){
        require(['modules/Task/controllers/create'], function(){
          App.Task.Create.Controller.createTask();
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

    App.on('task:create', function(create){
      App.navigate("tasks/create");
      API.createTask();
    });

    App.addInitializer(function(){
      new Routers({
        controller: API
      });
    })

  });

});