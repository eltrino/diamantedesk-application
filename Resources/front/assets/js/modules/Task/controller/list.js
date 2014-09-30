define(['app', 'modules/Task/view/list'], function(App, Task){

  App.module('Task.List', function(List, App, Backbone, Marionette, $, _){

    List.Controller = {
      listTasks: function(){
        require(['models/Task'], function(){
          var task = App.request("task:model");
          var taskListView = new List.Items({
            collection: task
          });
          App.main.show(taskListView);
        });
      }
    }

  });

});