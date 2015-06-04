define([
  'app',
  'config',
  'moment',
  'cryptojs.md5',
  'tpl!../templates/item.ejs'], function(App, Config, moment, MD5, itemTemplate){

  return App.module('Ticket.View.Comment.List', function(List, App, Backbone, Marionette, $, _){

    List.ItemView = Marionette.ItemView.extend({
      tagName: 'li',
      className: 'comments-item',
      template: itemTemplate,

      initialize: function(){
        this.listenTo(this.model, 'change', this.render );
      },

      templateHelpers: function(){
        var author = this.model.get('authorModel'),
            avatar_url, author_name;
        if(author){
          avatar_url = author.get('avatar') || 'http://www.gravatar.com/avatar/'+ MD5(author.get('email')) +'?s=32&d=identicon';
          author_name = author.get('name') || author.get('email');
        } else {
          avatar_url = 'http://www.gravatar.com/avatar/undefined?s=32&d=mm';
          author_name = 'Loading...';
        }
        return {
          is_author: this.model.get('author') === App.session.get('id'),
          avatar_url : avatar_url,
          author_name : author_name,
          attach_link : function(hash){
            return Config.baseUrl.replace('diamantefront','desk') + 'attachments/download/file/' + hash;
          },
          created_relative : moment(this.model.get('created_at')).fromNow(),
          created_at : moment(this.model.get('created_at')).format('lll')
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
      className: 'comments-list list-unstyled',
      childView: List.ItemView
    });

  });

});