define([
  'app',
  './list',
  'tpl!../templates/layout.ejs'], function(App, List, layoutTemplate){

  return App.module('Ticket.View.Watcher', function(Watcher, App, Backbone, Marionette, $, _){

    Watcher.LayoutView = Marionette.LayoutView.extend({
      template: layoutTemplate,

      regions: {
        listRegion: '#watchers-list'
      },

      ui: {
        addButton: '.js-add-watcher'
      },

      events: {
        'click @ui.addButton' : 'addWatcher'
      },

      addWatcher: function(e){
        e.preventDefault();
        this.trigger('watcher:add');
      },

      List: List.CollectionView

    });

  });

});