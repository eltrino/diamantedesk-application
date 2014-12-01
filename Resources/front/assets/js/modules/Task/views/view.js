define([
  'app',
  'tpl!../templates/view.ejs',
  'tpl!../templates/missing-view.ejs'], function(App, taskViewTemplate, missingTaskViewTemplate){

  return App.module('Task.View', function(View, App, Backbone, Marionette, $, _){

    View.MissingView = Marionette.ItemView.extend({
      template: missingTaskViewTemplate
    });

    View.ItemView = Marionette.ItemView.extend({
      className: 'ticket-view',
      template: taskViewTemplate,

      events : {
        "click .js-task-list" : "listTasks",
        "click .js-edit-ticket" : "editTicket"
      },

      listTasks : function(e){
        e.preventDefault();
        App.trigger('task:list');
      },

      editTicket : function(e){
        e.preventDefault();
        App.trigger('task:edit', this.model.get('id'), this.model);
      }
    });

  });

});