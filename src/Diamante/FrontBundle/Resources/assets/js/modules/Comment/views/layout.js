define([
  'app',
  './list',
  './create',
  'tpl!../templates/layout.ejs'], function(App, List, Create, layoutTemplate){

  return App.module('Ticket.View.Comment', function(Comment, App, Backbone, Marionette, $, _){

    Comment.LayoutView = Marionette.LayoutView.extend({
      template: layoutTemplate,

      regions: {
        listRegion: '#comments-list',
        formRegion: '#comments-form'
      },

      List: List.CollectionView,
      Form: Create.ItemView

    });

  });

});