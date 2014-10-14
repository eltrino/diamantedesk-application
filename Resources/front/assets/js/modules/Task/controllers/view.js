define(['app', 'modules/Task/views/list'], function(App, Task){

  App.module('Task.View', function(View, App, Backbone, Marionette, $, _){

    View.Controller = {

      viewTask: function(id){
        require(['modules/Task/models/task', 'modules/Task/views/view'], function(){
          var collection = App.request("task:collection");
          var model = collection.get(id);
          var taskView;
          if(model !== undefined){
            taskView = new View.ItemView({
              model : model
            });
          } else {
            taskView = new View.MissingView();
          }


          App.main.show(taskView);
        });
      }

    }

  });

});