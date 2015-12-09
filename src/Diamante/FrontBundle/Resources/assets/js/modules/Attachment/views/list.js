define([
  'app',
  'config',
  'tpl!../templates/item.ejs'], function(App, Config, itemTemplate){

  return App.module('Ticket.View.Attachment.List', function(List, App, Backbone, Marionette, $, _){

    List.ItemView = Marionette.ItemView.extend({
      tagName: 'li',
      template: itemTemplate,

      ui: {
        deleteButton: '.js-delete-attachment'
      },

      events: {
        'click @ui.deleteButton' : 'deleteAttachment'
      },

      deleteAttachment: function(e){
        e.preventDefault();
        this.trigger('attachment:delete', this.model);
      }

    });

    List.CollectionView = Marionette.CollectionView.extend({
      tagName: 'ul',
      className: 'attachments-list list-unstyled',
      childView: List.ItemView
    });

  });

});