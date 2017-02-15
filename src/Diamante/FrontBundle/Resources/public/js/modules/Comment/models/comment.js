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
          errors.content = __('Error_required');
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
