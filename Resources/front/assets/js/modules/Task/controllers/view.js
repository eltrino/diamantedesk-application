define(function(){

  App.module('Task.View', function(View, App, Backbone, Marionette, $, _){

    View.Controller = {

      viewTask: function(id){
        require(['modules/Task/models/task', 'modules/Task/views/view'], function(){
          var fetching = App.request("task:collection");
          $.when(fetching).done(function(collection){
            var model = collection.get(id);
            var taskView;
            if(model !== undefined){
              taskView = new View.ItemView({
                model : model
              });
            } else {
              taskView = new View.MissingView();
            }

            App.MainRegion.show(taskView);
          });

        });
      }

    }

  });

});