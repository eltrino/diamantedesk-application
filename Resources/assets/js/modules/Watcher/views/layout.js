define([
  'app',
  './list',
  'tpl!../templates/layout.ejs'], function(App, List, layoutTemplate){

  return App.module('Ticket.View.Attachment', function(Watcher, App, Backbone, Marionette, $, _){

    Watcher.LayoutView = Marionette.LayoutView.extend({
      template: layoutTemplate,

      regions: {
        listRegion: '#watchers-list'
      },

      List: List.CollectionView

    });

  });

});