define([
  'app',
  'tpl!../templates/list-item.ejs',
  'tpl!../templates/list.ejs'], function(App, listItemTemplate, listTemplate){

  return App.module('Ticket.List', function(List, App, Backbone, Marionette, $, _){

    List.ItemView = Marionette.ItemView.extend({
      tagName: "tr",
      template: listItemTemplate,

      templateHelpers: function(){
        return {
          created : new Date(this.model.get('created_at')).toLocaleDateString()
        };
      },

      events : {
        'click' : "viewClicked"
      },

      viewClicked: function(e){
        e.preventDefault();
        e.stopPropagation();
        this.trigger("ticket:view", this.model);
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