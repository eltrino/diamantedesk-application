define(['app'], function(App){

  return App.module('Ticket.View.Comment.Create', function(Create, App, Backbone, Marionette, $, _){

    Create.Controller = function(options){
      require([
        'Comment/models/comment',
        'Comment/models/attachment',
        'Comment/views/create'], function(CommentModels, AttachmentModels){

        var commentModel = new CommentModels.Model({},{ ticket : options.ticket }),
            commentCollection = options.collection,
            attachmentCollection = new AttachmentModels.Collection(),
            formView = new Create.LayoutView({ model: commentModel, attachmentCollection: attachmentCollection }),
            onSuccess = function(model){
              formView.hideLoader();
              commentCollection.add(model);
              App.trigger('message:show', {
                status:'success',
                text: 'Comment was posted successfully'
              });
              formView.destroy();
              Create.Controller(options);
            };
        formView.on('form:submit', function(data){
          formView.showLoader();
          App.request('user:model:current').done(function(user){
            commentModel.set({
              'author': 'diamante_' + user.get('id')
            }, { 'silent': true });
            commentModel.save(data, {
              success : function(model){
                if(attachmentCollection.length){
                  attachmentCollection.save({
                    comment : commentModel,
                    success : function(data){
                      model.set('attachments', data);
                      onSuccess(model);
                    }
                  });
                } else {
                  onSuccess(model);
                }
              },
              error : function(model, xhr){
                formView.hideLoader();
                App.alert({
                  title: "Create Comment Error",
                  xhr : xhr
                });
              }
            });
            if(!commentModel.isValid()){
              formView.hideLoader();
            }
          });
        });
        formView.on('attachment:add', function(data){
          attachmentCollection.add(data);
        });
        formView.on('attachment:delete', function(model){
          attachmentCollection.remove(model);
        });

        options.parentView.formRegion.show(formView);

      });
    };

  });

});