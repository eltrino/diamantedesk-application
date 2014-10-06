define(['app'], function(App, Task){

  App.module('Task.List', function(List, App, Backbone, Marionette, $, _){

    List.Controller = {

      listTasks: function(){
        require(['modules/Task/models/task', 'modules/Task/views/list'], function(){
          var task = App.request("task:model");
          var taskListView = new List.Items({
            collection: task
          });

          taskListView.on("childview:task:show", function(childView, model){
            require(['modules/Task/controllers/show'], function(){
              App.Task.Show.Controller.showTask(model);
            });
          });

          App.main.show(taskListView);

        });
      }

    }

  });

});