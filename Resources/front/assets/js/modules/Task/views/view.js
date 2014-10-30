define([
  'tpl!modules/Task/templates/view.ejs',
  'tpl!modules/Task/templates/missing-view.ejs'], function(taskViewTemplate, missingTaskViewTemplate){

  App.module('Task.View', function(View, App, Backbone, Marionette, $, _){

    View.MissingView = Marionette.ItemView.extend({
      template: missingTaskViewTemplate
    });

    View.ItemView = Marionette.ItemView.extend({
      className: 'ticket-view',
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

});