define(['app'], function(App){

  return App.module('Ticket.View.Comment.Edit', function(Edit, App, Backbone, Marionette, $, _){

    Edit.Controller = function(commentModel, options){

      require([
        'Comment/models/attachment',
        'Comment/views/form'], function(AttachmentModels, Form){

        var parentView = options.parentView,
            attachmentCollection = new AttachmentModels.Collection(commentModel.get('attachments'), { comment : commentModel }),
            formView = new Form.LayoutView({ model: commentModel, attachmentCollection : attachmentCollection }),
            destroyAttachments = [];

        formView.on('form:submit', function(data){
          var request = [],
              addAttachments = new AttachmentModels.Collection(
                attachmentCollection.filter(function(model){ return model.isNew(); }),
                { comment : commentModel }
              );
          if(addAttachments.length){
            request.push(addAttachments.save());
          }
          destroyAttachments.forEach(function(model){
            request.push(model.destroy());
          });
          $.when.apply($, request).done(function(){
            commentModel.save(data,{
              wait: true,
              success : function(model){
                require(['Comment/controllers/create'],function(Create){
                  Create.Controller(options);
                });
              },
              error : function(model, xhr){
                App.alert({
                  title: "Create Comment Error",
                  xhr : xhr
                });
              }
            });
          }).fail(function(xhr){
            console.warn(arguments);
            App.alert({
              title: "Edit Attachments Error",
              xhr : xhr
            });
          });
        });

        formView.on('attachment:add', function(data){
          attachmentCollection.add(data);
        });
        formView.on('attachment:delete', function(model){
          if(!model.isNew()){
            destroyAttachments.push(model);
          }
          attachmentCollection.remove(model);
        });

        parentView.formRegion.show(formView);

      });
    };

  });

});