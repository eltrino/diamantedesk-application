define(function(){

  App.module('Task.List', function(List, App, Backbone, Marionette, $, _){

    List.TaskController = function(){

      require(['modules/Task/models/task', 'modules/Task/views/list'], function(){

        var fetching = App.request("task:collection");

        $.when(fetching).done(function(collection){

          var taskListView = new List.CompositeView({
            collection: collection
          });

          taskListView.on("childview:task:view", function(childView, model){
            App.trigger('task:view', model.get('id'));
          });

          App.MainRegion.show(taskListView);

        })

      });

    }

  });

});