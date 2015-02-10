define([
  'app',
  'config'], function(App, Config){

  return App.module('Ticket.View.Attachment', function(Attachment, App, Backbone, Marionette, $, _){

    Attachment.Model = Backbone.Model.extend({
      urlRoot: Config.apiUrl + '/desk/tickets/{ticketId}/attachments',

      initialize: function(attr, options){
        if(options.ticket){
          this.urlRoot = this.urlRoot.replace('{ticketId}', options.ticket.get('id'));
        } else {
          App.debug('error','"options.ticket" is required');
        }
      }
    });

    Attachment.Collection = Backbone.Collection.extend({
      url: Config.apiUrl+ '/desk/tickets/{ticketId}/attachments',
      model: Attachment.Model,

      initialize: function(attr, options){
        if(options.ticket){
          this.url = this.url.replace('{ticketId}', options.ticket.get('id'));
        } else {
          App.debug('error','"options.ticket" is required');
        }
      }

    });

  });

});
