define([
  'app',
  'config'], function(App, Config){

  return App.module('Ticket.View.Comment', function(Comment, App, Backbone, Marionette, $, _){

    Comment.Model = Backbone.Model.extend({
      urlRoot : Config.apiUrl + '/desk/comments',
      initialize : function(attr, options){

        if(attr && attr.author){
          App.request('user:model', attr.author).done(function(user){
            this.set('authorFullName', user.get('firstName') + ' ' + user.get('lastName'));
          }.bind(this));
        }

        if(options.ticket){
          this.set({
            ticket : options.ticket.get('id'),
            ticketStatus : options.ticket.get('status')
          });
        }
      },
      validate: function(attrs, options){
        var errors = {};
        if(!attrs.content) {
          errors.content = "can't be blank";
        }
        if(!_.isEmpty(errors)){
          return errors;
        }
      }
    });

    Comment.Collection = Backbone.Collection.extend({
      url: Config.apiUrl+ '/desk/comments',
      model: Comment.Model
    });

  });

});
