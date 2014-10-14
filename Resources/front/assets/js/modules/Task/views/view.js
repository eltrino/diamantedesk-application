define(['app',
  'tpl!modules/Task/templates/view.ejs',
  'tpl!modules/Task/templates/missing-view.ejs'], function(App, taskViewTemplate, missingTaskViewTemplate){

  App.module('Task.View', function(View, App, Backbone, Marionette, $, _){

    View.MissingView = Marionette.ItemView.extend({
      template: missingTaskViewTemplate
    });

    View.ItemView = Marionette.ItemView.extend({
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

  return App.Task.View;

});