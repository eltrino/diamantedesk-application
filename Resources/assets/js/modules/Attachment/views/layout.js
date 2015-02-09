define([
  'app',
  './list',
  './dropzone',
  'tpl!../templates/layout.ejs'], function(App, List, DropZone, layoutTemplate){

  return App.module('Ticket.View.Attachment', function(Attachment, App, Backbone, Marionette, $, _){

    Attachment.LayoutView = Marionette.LayoutView.extend({
      template: layoutTemplate,

      regions: {
        listRegion: '#attachments-list',
        dropRegion: '#attachments-drop'
      },

      List: List.CollectionView,
      Drop: DropZone.ItemView

    });

  });

});