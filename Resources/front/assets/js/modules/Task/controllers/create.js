define(function(){

  return App.module('Task.Create', function(Create, App, Backbone, Marionette, $, _){

    Create.TaskController = function(){

      require(['modules/Task/models/task', 'modules/Task/views/create'], function(Models, CreateView){

        var isSuccess = false,
            newTaskModel = new Models.TaskModel(),
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
          if(!isSuccess){
            Backbone.history.history.back();
          }
        });

        modalCreateView.on('modal:submit', function(data){
          newTaskModel.save(data, {
            success : function(resultModel){
              isSuccess = true;
              App.trigger('task:view', resultModel.get('id'));
              modalCreateView.$el.modal('hide');
            }
          });
        });

        App.DialogRegion.show(modalCreateView);
        modalCreateView.ModalBody.show(newTaskView);

      });

    }

  });

});