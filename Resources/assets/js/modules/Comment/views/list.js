define([
  'app',
  'tpl!../templates/list.ejs',
  'tpl!../templates/item.ejs'], function(App, listTemplate, itemTemplate){

  return App.module('Ticket.View.Comment.List', function(Comment, App, Backbone, Marionette, $, _){

    Comment.LayoutView = Marionette.LayoutView.extend({
      template : listTemplate,

      regions : {
        ListRegion : '#comments-list',
        FormRegion : '#comments-form'
      }

    });

    Comment.ItemView = Marionette.ItemView.extend({
      template : itemTemplate,
      initialize : function(){
        this.listenTo(this.model, 'change:authorFullName', this.render );
      }
    });

    Comment.CollectionView = Marionette.CollectionView.extend({
      childView : Comment.ItemView
    });

  });

});