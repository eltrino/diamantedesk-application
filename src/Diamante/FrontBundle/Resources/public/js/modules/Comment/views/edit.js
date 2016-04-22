define([
  'app',
  'Comment/views/create'], function(App, CreateView){

  return App.module('Ticket.View.Comment.Edit', function(Edit, App, Backbone, Marionette, $, _){

    Edit.LayoutView = CreateView.LayoutView.extend({
      className : 'comments-form',
      regions : {
        attachmentRegion: '#comment-edit-attachment',
        dropRegion : '#comment-edit-attachment-drop'
      },
      triggers: {
        "click .js-cancel": "edit:cancel"
      },
      initialize : function(options){
        this.attachmentCollection = options.attachmentCollection;
      },
      onShow : function(){
        CreateView.LayoutView.prototype.onShow.call(this);
      }
    });

  });

});