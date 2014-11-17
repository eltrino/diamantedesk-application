define(function(){

  App.module('Task.Create', function(Create, App, Backbone, Marionette, $, _){

    Create.TaskController = function(){

      require(['modules/Task/models/task', 'modules/Task/views/create'], function(){

        var newTask = new App.Task.Models.TaskModel(),
            newTaskView = new Create.ItemView({
              model: newTask
            }),
            modalCreateView = new Create.ModalView({
              model: new Backbone.Model({title: 'Add New Ticket'})
            });

        modalCreateView.on('show', function(){
          this.$el.modal();
        });

        modalCreateView.on('modal:closed', function(){
          Backbone.history.history.back();
        });

        App.DialogRegion.show(modalCreateView);
        modalCreateView.ModalBody.show(newTaskView);

      });

    }

  });

});