define(function(){

  App.module('Task.Edit', function(Edit, App, Backbone, Marionette, $, _){

    Edit.TaskController = function(id){

      require(['modules/Task/models/task', 'modules/Task/views/create'], function(){

        var fetching = App.request("task:collection");

        $.when(fetching).done(function(collection){

          var model = collection.get(id),
              taskEditView;

          if(model === undefined){

            taskEditView = new App.Task.View.MissingView();
            App.MainRegion.show(taskEditView);

          } else {

            taskEditView = new App.Task.Create.ItemView({
              model : model
            });

            taskEditView.on('show', function(){
              this.$el.modal();
            });

            taskEditView.on('modal:closed', function(){
              Backbone.history.history.back();
              this.destroy();
            });

            App.DialogRegion.show(taskEditView);
          }

        });


      });

    }

  });

});