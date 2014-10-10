define(['app'], function(App, Task){

  App.module('Task.List', function(List, App, Backbone, Marionette, $, _){

    List.Controller = {

      listTasks: function(){
        require(['modules/Task/models/task', 'modules/Task/views/list'], function(){
          var tasksList = App.request("task:collection");
          var taskListView = new List.CompositeView({
            collection: tasksList
          });

          taskListView.on("childview:task:show", function(childView, model){
            App.trigger('task:show', model.get('id'));
          });

          App.main.show(taskListView);

        });
      }

    }

  });

});