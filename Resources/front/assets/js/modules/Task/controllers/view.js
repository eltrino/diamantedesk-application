define(function(){

  App.module('Task.View', function(View, App, Backbone, Marionette, $, _){

    View.TaskController = function(id){

      require(['modules/Task/models/task', 'modules/Task/views/view'], function(){

        var fetching = App.request("task:collection");

        $.when(fetching).done(function(collection){

          var model = collection.get(id),
              taskView;

          if(model !== undefined){
            taskView = new View.ItemView({
              model : model
            });
          } else {
            taskView = new View.MissingView();
          }

          taskView.on("task:edit", function(model){
            App.trigger('task:edit', model.get('id'));
          });

          App.MainRegion.show(taskView);

        });

      });

    }

  });

});