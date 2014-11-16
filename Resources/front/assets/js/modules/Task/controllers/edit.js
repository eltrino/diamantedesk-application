define(function(){

  App.module('Task.Edit', function(Edit, App, Backbone, Marionette, $, _){

    Edit.TaskController = function(id){

      require(['modules/Task/models/task', 'modules/Task/views/edit'], function(){

        var model = App.request("task:model", id);

        model.on('sync', function(){
          var editTask = new App.Task.Models.TaskCollection([model]),
              taskEditView = new Edit.ModalView({
                model: new Backbone.Model({title: 'Edit Ticket ' + model.get('shortcode') + "-" + model.id}),
                collection : editTask
              });

          taskEditView.on('show', function(){
            this.$el.modal();
          });

          taskEditView.on('modal:closed', function(){
            Backbone.history.history.back();
            this.destroy();
          });

          App.DialogRegion.show(taskEditView);
        });

        model.on('error', function(){
          var taskMissingView = new App.Task.View.MissingView();
          App.MainRegion.show(taskMissingView);
        });

      });

    }

  });

});