define(function(){

  App.module('Task.View', function(View, App, Backbone, Marionette, $, _){

    View.TaskController = function(id){

      require(['modules/Task/models/task', 'modules/Task/views/view'], function(){

        var model = App.request("task:model", id);

        model.on('sync', function(){
          var taskView = new View.ItemView({
            model : model
          });
          App.MainRegion.show(taskView);
        });

        model.on('error', function(){
          var missingView = new View.MissingView();
          App.MainRegion.show(missingView);
        });

      });

    }

  });

});