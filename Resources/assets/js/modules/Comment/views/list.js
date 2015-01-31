define([
  'app',
  'tpl!../templates/list.ejs',
  'tpl!../templates/item.ejs'], function(App, listTemplate, itemTemplate){

  return App.module('Ticket.View.Comment.List', function(Comment, App, Backbone, Marionette, $, _){

    Comment.LayoutView = Marionette.LayoutView.extend({
      template: listTemplate,

      regions: {
        listRegion: '#comments-list',
        formRegion: '#comments-form'
      }

    });

    Comment.ItemView = Marionette.ItemView.extend({
      template: itemTemplate,

      initialize: function(){
        this.listenTo(this.model, 'change:authorFullName', this.render );
        this.listenTo(this.model, 'change:content', this.render );
      },

      templateHelpers: function(){
        return {
          created: new Date(this.model.get('created_at')).toLocaleDateString() + ' ' + new Date(this.model.get('created_at')).toLocaleTimeString(),
          isAuthor: this.model.get('author') === App.session.get('id')
        };
      },

      ui: {
        editButton: '.js-edit-comment',
        deleteButton: '.js-delete-comment'
      },

      events: {
        'click @ui.editButton' : 'editComment',
        'click @ui.deleteButton' : 'deleteComment'
      },

      editComment: function(){
        this.trigger('comment:edit', this.model);
      },

      deleteComment: function(){
        this.trigger('comment:delete', this.model);
      }


    });

    Comment.CollectionView = Marionette.CollectionView.extend({
      childView: Comment.ItemView
    });

  });

});