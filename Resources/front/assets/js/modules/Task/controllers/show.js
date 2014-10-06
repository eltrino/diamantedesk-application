define(['app', 'modules/Task/views/list'], function(App, Task){

  App.module('Task.Show', function(Show, App, Backbone, Marionette, $, _){

    Show.Controller = {

      showTask: function(model){
        require(['modules/Task/views/show'], function(){
          var taskView = new Show.Task({
            model : model
          });
          App.main.show(taskView);
        });
      }

    }

  });

});