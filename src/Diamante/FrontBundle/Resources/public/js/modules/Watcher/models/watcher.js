define([
  'app',
  'config'], function(App, Config){

  function validateEmail(email) {
    return !!String(email).match(/^\s*[\w\-\+_]+(?:\.[\w\-\+_]+)*@[\w\-\+_]+\.[\w\-\+_]+(?:\.[\w\-\+_]+)*\s*$/);
  }

  return App.module('Ticket.View.Watcher', function(Watcher, App, Backbone, Marionette, $, _){

    var trim = $.trim;

    Watcher.Model = Backbone.Model.extend({
      urlRoot: Config.apiUrl + '/desk/tickets/{ticketId}/watchers',

      initialize: function(attr, options){
        if(options.ticket){
          this.urlRoot = this.urlRoot.replace('{ticketId}', options.ticket.get('id'));
        }
      },

      validate: function(attrs, options){
        var errors = {};
        if(!validateEmail(attrs.email)){
          errors.email =  __('diamante_front.session.model.error.email_format', {email: attrs.email});
        }
        if(!trim(attrs.email)) {
          errors.email = __('diamante_front.watcher.model.error.required');
        }
        if(!_.isEmpty(errors)){
          return errors;
        }
      }

    });

    Watcher.Collection = Backbone.Collection.extend({
      url: Config.apiUrl + '/desk/tickets/{ticketId}/watchers',
      model: Watcher.Model,

      initialize: function(attr, options){
        if(options && options.ticket){
          this.ticket = options.ticket;
          this.url = this.url.replace('{ticketId}', options.ticket.get('id'));
        }
      }
    });

  });

});
