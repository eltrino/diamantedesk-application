define(function(){

  App.module('Task.Create', function(Create, App, Backbone, Marionette, $, _){

    Create.TaskController = function(){

      require(['modules/Task/models/task', 'modules/Task/views/create'], function(){

        var newTaskModel = new App.Task.Models.TaskModel(),
            newTaskView = new Create.ItemView({
              model: newTaskModel
            }),
            modalCreateView = new Create.ModalView({
              title: 'Add New Ticket'
            });

        modalCreateView.on('show', function(){
          this.$el.modal();
        });

        modalCreateView.on('modal:closed', function(){
          Backbone.history.history.back();
        });

        modalEditView.on('modal:submit', function(data){
          newTaskModel.save(data);
        });

        App.DialogRegion.show(modalCreateView);
        modalCreateView.ModalBody.show(newTaskView);

      });

    }

  });

});