define(function(){

  App.module('Task.List', function(List, App, Backbone, Marionette, $, _){

    List.TaskController = function(){

      require(['modules/Task/models/task', 'modules/Task/views/list'], function(){

        var request = App.request("task:collection");

        request.done(function(taskCollection){
          var taskListView = new List.CompositeView({
            collection: taskCollection
          });

          taskListView.on("childview:task:view", function(childView, model){
            App.trigger('task:view', model.get('id'));
          });

          App.MainRegion.show(taskListView);
        });

      });

    }

  });

});