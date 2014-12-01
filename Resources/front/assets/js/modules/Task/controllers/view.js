define(['app'], function(App){

  return App.module('Task.View', function(View, App, Backbone, Marionette, $, _){

    View.TaskController = function(id){

      require(['modules/Task/models/task', 'modules/Task/views/view'], function(){

        App.request("task:model", id).done(function(taskModel){

          var taskView = new View.ItemView({
            model : taskModel
          });
          App.MainRegion.show(taskView);

        }).fail(function(){

          var missingView = new View.MissingView();
          App.MainRegion.show(missingView);

        });

      });

    };

  });

});