define([
  'app',
  'tpl!../templates/item.ejs'], function(App, itemTemplate){

  return App.module('Ticket.View.Watcher.List', function(Watcher, App, Backbone, Marionette, $, _){

    Watcher.ItemView = Marionette.ItemView.extend({
      tagName: 'li',
      className: 'watchers-item',
      template: itemTemplate,

      ui: {
        "deleteButton": '.js-watcher-delete'
      },

      events: {
        'click @ui.deleteButton' : 'deleteWatcher'
      },

      deleteWatcher: function(e){
        e.preventDefault();
        this.trigger('watcher:delete', this.model);
      },

      templateHelpers: function(){
        return {
          username :  this.model.get('name') || this.model.get('email')
        };
      }

    });

    Watcher.CollectionView = Marionette.CollectionView.extend({
      tagName: 'ul',
      className: 'watchers-list list-unstyled',
      childView: Watcher.ItemView
    });

  });

});