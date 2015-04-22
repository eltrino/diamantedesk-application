define([
  'app',
  'config'], function(App, Config){

  return App.module('Ticket.View.Comment', function(Comment, App, Backbone, Marionette, $, _){

    var trim = $.trim;

    Comment.Model = Backbone.Model.extend({
      urlRoot: Config.apiUrl + '/desk/comments',
      defaults: {
        content: ''
      },

      initialize: function(attr, options){
        this.fetchAuthor();
        if(options.ticket){
          this.set({
            ticket : options.ticket.get('id'),
            ticketStatus : options.ticket.get('status')
          });
        }
      },

      validate: function(attrs, options){
        var errors = {};
        if(!trim(attrs.content)) {
          errors.content = "Can't be blank";
        }
        if(!_.isEmpty(errors)){
          return errors;
        }
      },

      fetchAuthor : function(){
        var model = this,
            author;
        if(model.get('author')){
          require(['Comment/models/author'], function(Author){
            author = new Author.Model({}, { comment : model });
            author.fetch({
              success: function(){
                model.set({
                  'authorModel': author
                });
              }
            });
          });
        }
      }

    });

    Comment.Collection = Backbone.Collection.extend({
      url: Config.apiUrl+ '/desk/comments',
      model: Comment.Model
    });

  });

});
