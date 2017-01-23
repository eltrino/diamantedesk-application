define(['app'], function(App){

  return App.module('Ticket.View.Comment.Edit', function(Edit, App, Backbone, Marionette, $, _){

    Edit.Controller = function(commentModel, commentView, options){

      require([
        'Comment/models/attachment',
        'Comment/views/edit'], function(AttachmentModels){

        var parentView = options.parentView,
            attachmentCollection = new AttachmentModels.Collection(commentModel.get('attachments'), { comment : commentModel }),
            formView = new Edit.LayoutView({ model: commentModel, attachmentCollection : attachmentCollection }),
            destroyAttachments = [];

        if(parentView.isEditing){
          parentView.isEditing.trigger('edit:cancel', {
            view : parentView.isEditing
          });
        }

        formView.on('form:submit', function(data){
          formView.showLoader();
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
                commentView.$el.removeClass('is-editing');
                App.trigger('message:show', {
                  status:'success',
                  text: 'Your comment has been edited'
                });
              },
              error : function(model, xhr){
                formView.hideLoader();
                App.alert({
                  title: __('diamante_front.comment.controller.alert.create_error.title'),
                  xhr : xhr
                });
              }
            });
            if(!commentModel.isValid()){
              formView.hideLoader();
            }
          }).fail(function(xhr){
            console.warn(arguments);
            formView.hideLoader();
            App.alert({
              title: __('diamante_front.comment.controller.alert.edit_success.title'),
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

        formView.on('edit:cancel', function(arg){
          parentView.isEditing = false;
          commentView.$el.removeClass('is-editing');
          arg.view.destroy();
          commentView.render();
        });

        commentView.$el.append(formView.el).addClass('is-editing');
        parentView.isEditing = formView;

        formView.render();
        formView.triggerMethod('show');

      });
    };

  });

});