define(function(){

  App.module('Task.Create', function(Create, App, Backbone, Marionette, $, _){

    Create.Controller = {

      createTask: function(){

        require(['modules/Task/models/task', 'modules/Task/views/create'], function(){

          var newContact = new App.Task.Models.TaskModel(),
              taskCreateView = new Create.ItemView({
                model : newContact
              });

          taskCreateView.on('show', function(){
            this.$el.modal();
          });

          taskCreateView.on('modal:closed', function(){
            Backbone.history.history.back();
            this.destroy();
          });

          App.DialogRegion.show(taskCreateView);

        });

      }

    }

  });

});