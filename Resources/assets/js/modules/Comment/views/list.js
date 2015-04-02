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
        this.listenTo(this.model, 'change:authorName', this.render );
        this.listenTo(this.model, 'change:content', this.render );
        this.listenTo(this.model, 'change:attachments', this.render );
      },

      templateHelpers: function(){
        return {
          avatar_url : 'http://www.gravatar.com/avatar/' + MD5(this.model.get('authorEmail')),
          isAuthor: this.model.get('author') === App.session.get('id'),
          attach_link : function(hash){
            return Config.baseUrl.replace('diamantefront','desk') + 'attachments/download/file/' + hash;
          },
          content: this.model.get('content').replace(/\n/g,'<br />'),
          created_relative : moment(this.model.get('created_at')).fromNow(),
          created_at : moment(this.model.get('created_at')).format('lll')
          //created_at : moment(this.model.get('created_at')).locale(navigator.language).format('lll')
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