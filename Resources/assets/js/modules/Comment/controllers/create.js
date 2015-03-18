define(['app'], function(App){

  return App.module('Ticket.View.Comment.Create', function(Create, App, Backbone, Marionette, $, _){

    Create.Controller = function(options){
      require([
        'Comment/models/comment',
        'Comment/models/attachment',
        'Comment/views/form'], function(CommentModels, AttachmentModels, Form){

        var commentModel = new CommentModels.Model({},{ ticket : options.ticket }),
            commentCollection = options.collection,
            attachmentCollection = new AttachmentModels.Collection(),
            formView = new Form.LayoutView({ model: commentModel, attachmentCollection: attachmentCollection });

        formView.on('form:submit', function(data){
          App.request('user:model:current').done(function(user){
            commentModel.set({
              'author': 'diamante_' + user.get('id'),
              'authorFullName' : user.get('firstName') + ' ' + user.get('lastName')
            }, { 'silent': true });
            commentModel.save(data, {
              success : function(model){
                if(attachmentCollection.length){
                  attachmentCollection.save({
                    comment : commentModel,
                    success : function(data){
                      model.set('attachments', data);
                      commentCollection.add(model);
                      Create.Controller(options);
                    }
                  });
                } else {
                  commentCollection.add(model);
                  Create.Controller(options);
                }
              },
              error : function(model, xhr){
                App.alert({
                  title: "Create Comment Error",
                  xhr : xhr
                });
              }
            });
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