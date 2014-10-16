define(['app',
  'tpl!modules/Task/templates/list-item.ejs',
  'tpl!modules/Task/templates/list.ejs'], function(App, listItemTemplate, listTemplate){

  App.module('Task.List', function(List, App, Backbone, Marionette, $, _){

    List.ItemView = Marionette.ItemView.extend({
      tagName: "tr",
      template: listItemTemplate,

      events : {
        'click' : "viewClicked"
      },

      viewClicked: function(e){
        e.preventDefault();
        e.stopPropagation();
        this.trigger("task:view", this.model);
      }
    });

    List.CompositeView = Marionette.CompositeView.extend({
      tagName: "table",
      template: listTemplate,
      id: "task-list",
      className: "table table-hover table-bordered",
      childViewContainer: "tbody",
      childView: List.ItemView
    });

  });

  return App.Task.List;

});