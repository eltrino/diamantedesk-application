define(['app'], function(App){

  return App.module('Ticket.View.Attachment.List', function(List, App, Backbone, Marionette, $, _){

    List.Controller = function(options){

      var attachmentCollection = options.collection,
          parentView = options.parentView,
          attachmentCollectionView = new List.CollectionView({
            collection : attachmentCollection
          });

      attachmentCollectionView.on('childview:attachment:delete', function(childView, attachmentModel){
        attachmentModel.destroy({
          wait: true,
          error : function(model, xhr){
            App.alert({
              title: "Delete Attachment Error",
              xhr: xhr
            });
          }
        });
      });

      parentView.listRegion.show(attachmentCollectionView);

    };

  });

});