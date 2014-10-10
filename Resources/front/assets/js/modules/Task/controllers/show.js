define(['app', 'modules/Task/views/list'], function(App, Task){

  App.module('Task.Show', function(Show, App, Backbone, Marionette, $, _){

    Show.Controller = {

      showTask: function(id){
        require(['modules/Task/models/task', 'modules/Task/views/show'], function(){
          var collection = App.request("task:collection");
          var model = collection.get(id);
          var taskView = new Show.ItemView({
            model : model
          });

          App.main.show(taskView);
        });
      }

    }

  });

});