define(function(){

  App.module('Task.Edit', function(Edit, App, Backbone, Marionette, $, _){

    Edit.TaskController = function(id){

      require([
        'modules/Task/models/task',
        'modules/Task/views/view',
        'modules/Task/views/edit'], function(){

        App.request("task:model", id).done(function(editTaskModel){

          var editTaskView = new Edit.ItemView({
                model: editTaskModel
              }),
              modalEditView = new Edit.ModalView({
                title: 'Edit Ticket ' + editTaskModel.get('shortcode') + "-" + editTaskModel.id
              });

          modalEditView.on('show', function(){
            this.$el.modal();
          });

          modalEditView.on('modal:closed', function(){
            Backbone.history.history.back();
          });

          modalEditView.on('modal:submit', function(data){
            editTaskModel.save(data);
          });

          App.DialogRegion.show(modalEditView);
          modalEditView.ModalBody.show(editTaskView);

        }).fail(function(){
          console.log(App.Task.View);
          var taskMissingView = new App.Task.View.MissingView();
          App.MainRegion.show(taskMissingView);

        });

      });

    }

  });

});