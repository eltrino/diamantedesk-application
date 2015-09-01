define([
  'app',
  'config'], function(App, Config){

  return App.module('Ticket.View.Attachment', function(Attachment, App, Backbone, Marionette, $, _){

    Attachment.startWithParent = false;

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
        this.forEach(function(model) { model.unset('base64'); });
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
          xhr: function() {
            var model = this;
            var xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener("progress", function(e){
              var percent = 0;
              if (e.lengthComputable) {
                percent = e.loaded / e.total * 100;
              } else {
                percent = 100;
              }
              model.trigger('progress', 'sending', percent);
              if(percent === 100) {
                setTimeout(function(){ model.trigger('progress', 'receiving', 0); }, 600);
              }
            }, false);
            xhr.addEventListener("progress", function(e){
              var percent = 0;
              if (e.lengthComputable) {
                percent = e.loaded / e.total * 100;
              } else {
                percent = 100;
              }
              model.trigger('progress', 'receiving', percent);
            }, false);
            return xhr;
          }.bind(this),
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
