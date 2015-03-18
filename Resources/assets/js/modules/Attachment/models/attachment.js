define([
  'app',
  'config'], function(App, Config){

  return App.module('Ticket.View.Attachment', function(Attachment, App, Backbone, Marionette, $, _){

    Attachment.Model = Backbone.Model.extend({
      urlRoot: Config.apiUrl + '/desk/tickets/{ticketId}/attachments',

      initialize: function(attr, options){
        if(options.ticket){
          this.urlRoot = this.urlRoot.replace('{ticketId}', options.ticket.get('id'));
        }
      }
    });

    Attachment.Collection = Backbone.Collection.extend({
      url: Config.apiUrl+ '/desk/tickets/{ticketId}/attachments',
      model: Attachment.Model,

      initialize: function(attr, options){
        if(options && options.ticket){
          this.ticket = options.ticket;
          this.url = this.url.replace('{ticketId}', options.ticket.get('id'));
        }
      },

      save : function(options){
        console.log(options);
        var attr = { attachmentsInput : this.toJSON()},
          settings = _.extend({
            ticket : this.ticket,
            success : function(){},
            error : function(){}
          }, options);
        if(settings.ticket){
          this.url = this.url.replace('{ticketId}', settings.ticket.get('id'));
        } else {
          App.debug('error','"options.ticket" is required');
        }

        return $.ajax({
          url: this.url,
          type:'post',
          data: attr,
          success: function(data){
            settings.success(data);
          },
          error: function(xhr){
            settings.error();
            App.alert({
              title: "Add Attachments Error",
              xhr : xhr
            });
          }
        });
      }

    });

  });

});
