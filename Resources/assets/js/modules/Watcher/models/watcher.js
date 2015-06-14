define([
  'app',
  'config'], function(App, Config){

  function validateEmail(email) {
    return !!String(email).match(/^\s*[\w\-\+_]+(?:\.[\w\-\+_]+)*@[\w\-\+_]+\.[\w\-\+_]+(?:\.[\w‌​\-\+_]+)*\s*$/);
  }

  return App.module('Ticket.View.Watcher', function(Watcher, App, Backbone, Marionette, $, _){

    var trim = $.trim;

    Watcher.Model = Backbone.Model.extend({
      urlRoot: Config.apiUrl + '/desk/tickets/{ticketId}/watchers',

      initialize: function(attr, options){
        console.log(attr,options);
        if(options.ticket){
          this.urlRoot = this.urlRoot.replace('{ticketId}', options.ticket.get('id'));
        }
      },

      validate: function(attrs, options){
        var errors = {};
        if(!validateEmail(attrs.email)){
          errors.email = '"' + attrs.email + '" is not a valid email';
        }
        if(!trim(attrs.email)) {
          errors.email = "Can't be blank";
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
