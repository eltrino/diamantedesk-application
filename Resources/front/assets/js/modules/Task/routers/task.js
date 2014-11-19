define(function(){

  return App.module('Task.Routers', function(Routers, App, Backbone, Marionette, $, _){

    Routers = Marionette.AppRouter.extend({
      appRoutes: {
        "tasks" : "listTasks",
        "tasks/create" : "createTask",
        "tasks/:id" : "viewTask",
        "tasks/:id/edit" : "editTask"
      }
    });

    var API = {
      listTasks: function(){
        require(['modules/Task/controllers/list'], function(List){
          List.TaskController();
        });
      },
      viewTask: function(id){
        require(['modules/Task/controllers/view'], function(View){
          View.TaskController(id);
        });
      },
      createTask: function(){
        require(['modules/Task/controllers/create'], function(Create){
          Create.TaskController();
        });
      },
      editTask: function(id){
        require(['modules/Task/controllers/edit'], function(Edit){
          Edit.TaskController(id);
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

    App.on('task:create', function(){
      App.navigate("tasks/create");
      API.createTask();
    });

    App.on('task:edit', function(id){
      App.navigate("tasks/"+ id + "/edit");
      API.editTask(id);
    });

    App.addInitializer(function(){
      new Routers({
        controller: API
      });
    })

  });

});