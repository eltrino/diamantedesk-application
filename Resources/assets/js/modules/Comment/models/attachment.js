define([
  'app',
  'config'], function(App, Config){

  return App.module('Ticket.View.Comment.Attachment', function(Attachment, App, Backbone, Marionette, $, _){

    Attachment.Model = Backbone.Model.extend({
      urlRoot: Config.apiUrl + '/desk/comments/{id}/attachments',
      initialize: function(attr, options){
        if(options.comment){
          this.urlRoot = this.urlRoot.replace('{id}', options.comment.id);
        }
      }
    });

    Attachment.Collection = Backbone.Collection.extend({
      url: Config.apiUrl + '/desk/comments/{id}/attachments',
      model : Attachment.Model,
      initialize: function(attr, options){
        if(options && options.comment){
          this.comment = options.comment;
          this.url = this.url.replace('{id}', options.comment.id);
        }
      },
      save : function(options){
        var attr = { attachmentsInput : this.toJSON()},
            settings = _.extend({
              comment : this.comment,
              success : function(){},
              error : function(){}
            }, options);
        if(settings.comment){
          this.url.replace('{id}', settings.comment.id);
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
