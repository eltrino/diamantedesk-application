define([
  'app',
  'tpl!../templates/item.ejs'], function(App, itemTemplate){

  return App.module('Ticket.View.Watcher.List', function(Watcher, App, Backbone, Marionette, $, _){

    Watcher.ItemView = Marionette.ItemView.extend({
      tagName: 'li',
      className: 'watchers-item',
      template: itemTemplate,

      templateHelpers: function(){
        var username = $.trim(this.model.get('first_name') + ' ' + this.model.get('last_name'));
        return {
          username :  username || this.model.get('email')
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