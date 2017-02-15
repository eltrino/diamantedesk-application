define(['app'], function(App){

  return App.module('Ticket.View.Attachment.List', function(List, App, Backbone, Marionette, $, _){

    List.Controller = function(options){

      var attachmentCollection = options.collection,
          parentView = options.parentView,
          attachmentCollectionView = new List.CollectionView({
            collection : attachmentCollection.sort()
          });

      attachmentCollectionView.on('childview:attachment:delete', function(childView, attachmentModel){
        attachmentModel.destroy({
          wait: true,
          success: function(){
            App.trigger('message:show', {
              status:'success',
              text: __('diamante_front.attachment.controller.message.delete_success')
            });
          },
          error : function(model, xhr){
            App.alert({
              title: __('diamante_front.attachment.controller.alert.delete_error.title'),
              xhr: xhr
            });
          }
        });
      });

      parentView.listRegion.show(attachmentCollectionView);

    };

  });

});