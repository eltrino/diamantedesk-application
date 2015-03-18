define([
  'app',
  'config'], function(App, Config){

  return App.module('Ticket.View.Comment.Author', function(Author, App, Backbone, Marionette, $, _){

    Author.Model = Backbone.Model.extend({
      urlRoot: Config.apiUrl + '/desk/comment/{id}/author',
      initialize: function(attr, options){
        this.urlRoot = this.urlRoot.replace('{id}', options.comment.get('id'));
      }
    });

  });

});
