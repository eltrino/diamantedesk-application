define([
  'app',
  'tpl!../templates/list-item.ejs',
  'tpl!../templates/list.ejs'], function(App, listItemTemplate, listTemplate){

  return App.module('Task.List', function(List, App, Backbone, Marionette, $, _){

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
      className: "table table-hover table-bordered",
      childViewContainer: "tbody",
      childView: List.ItemView
    });

  });

});