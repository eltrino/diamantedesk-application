define(['app',
  'tpl!modules/Task/templates/show.ejs'], function(App, taskViewTemplate, listTemplate){

  App.module('Task.Show', function(Show, App, Backbone, Marionette, $, _){

    Show.ItemView = Marionette.ItemView.extend({
      template: taskViewTemplate,

      events : {
        "click .js-task-list" : "listTasksHandler"
      },

      listTasksHandler : function(e){
        e.preventDefault();
        App.trigger('task:list');
      }
    });

  });

  return App.Task.Show;

});