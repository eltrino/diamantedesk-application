define(['app',
  'tpl!modules/Task/templates/show.ejs'], function(App, taskViewTemplate, listTemplate){

  App.module('Task.Show', function(Show, App, Backbone, Marionette, $, _){

    Show.Task = Marionette.ItemView.extend({
      template: taskViewTemplate
    });

  });

  return App.Task.Show;

});