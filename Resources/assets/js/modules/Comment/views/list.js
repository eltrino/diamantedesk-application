define([
  'app',
  'tpl!../templates/item.ejs'], function(App, itemTemplate){

  return App.module('Ticket.View.Comment.List', function(List, App, Backbone, Marionette, $, _){

    List.ItemView = Marionette.ItemView.extend({
      tagName: 'li',
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

    List.CollectionView = Marionette.CollectionView.extend({
      tagName: 'ul',
      className: 'list-unstyled',
      childView: List.ItemView
    });

  });

});