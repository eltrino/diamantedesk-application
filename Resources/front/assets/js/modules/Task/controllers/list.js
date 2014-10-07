define(['app'], function(App, Task){

  App.module('Task.List', function(List, App, Backbone, Marionette, $, _){

    List.Controller = {

      listTasks: function(){
        require(['modules/Task/models/task', 'modules/Task/views/list'], function(){
          var tasksList = App.request("task:model");
          var taskListView = new List.Items({
            collection: tasksList
          });

          taskListView.on("childview:task:show", function(childView, model){
            require(['modules/Task/controllers/show'], function(){
              App.trigger('task:show', model.get('id'));
            });
          });

          App.main.show(taskListView);

        });
      }

    }

  });

});