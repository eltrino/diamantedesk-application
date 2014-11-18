define(function(){

  App.module('Task.Edit', function(Edit, App, Backbone, Marionette, $, _){

    Edit.TaskController = function(id){

      require(['modules/Task/models/task', 'modules/Task/views/edit'], function(){

        var model = App.request("task:model", id);

        model.on('sync', function(){
          var editTaskView = new Edit.ItemView({
                model: model
              }),
              modalEditView = new Edit.ModalView({
                title: 'Edit Ticket ' + model.get('shortcode') + "-" + model.id
              });

          modalEditView.on('show', function(){
            this.$el.modal();
          });

          modalEditView.on('modal:closed', function(){
            Backbone.history.history.back();
          });

          modalEditView.on('modal:submit', function(data){
            model.save(data);
          });

          App.DialogRegion.show(modalEditView);
          modalEditView.ModalBody.show(editTaskView);

        });



        model.on('error', function(){
          var taskMissingView = new App.Task.View.MissingView();
          App.MainRegion.show(taskMissingView);
        });

      });

    }

  });

});