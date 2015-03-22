define([
  'app',
  'config'], function(App, Config){

  return App.module('Ticket.View.Comment', function(Comment, App, Backbone, Marionette, $, _){

    Comment.Model = Backbone.Model.extend({
      urlRoot: Config.apiUrl + '/desk/comments',
      defaults: {
        content: ''
      },

      initialize: function(attr, options){

        if(attr && attr.author){
          require(['Comment/models/author'], function(Author){
            var author = new Author.Model({}, { comment : this });
            author.fetch({
              success: function(){
                this.set({
                  'authorName': author.get('name'),
                  'authorEmail': author.get('email')
                });
              }.bind(this)
            });
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
          errors.content = "Can't be blank";
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
