define(function(){

  return App.module('Task.Edit', function(Edit, App, Backbone, Marionette, $, _){

    Edit.TaskController = function(id){

      require([
        'modules/Task/models/task',
        'modules/Task/views/edit'], function(Models, EditView){

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
            App.trigger('task:view', editTaskModel.get('id'));
          });

          modalEditView.on('modal:submit', function(data){
            editTaskModel.save(data, {
              success : function(resultModel){
                App.trigger('task:view', resultModel.get('id'));
                modalEditView.$el.modal('hide');
              }
            });
          });

          App.DialogRegion.show(modalEditView);
          modalEditView.ModalBody.show(editTaskView);

        }).fail(function(){

          var taskMissingView = new Edit.MissingView();
          App.MainRegion.show(taskMissingView);

        });

      });

    }

  });

});