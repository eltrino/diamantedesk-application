define(['app', 'tpl!modules/Task/template/task.ejs'], function(App, taskTemplate){

  App.module('Task.List', function(List, App, Backbone, Marionette, $, _){

    List.Item = Marionette.ItemView.extend({
      tagName: "li",
      template: taskTemplate
    });

    List.Items = Marionette.CollectionView.extend({
      tagName: "ul",
      className: "list-unstyled",
      childView: List.Item
    });

  });

  return App.Task.List;

});