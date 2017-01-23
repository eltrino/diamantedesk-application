define(['app'], function(App){

  return App.module('Ticket.View.Comment.List', function(List, App, Backbone, Marionette, $, _){

    List.Controller = function(options){

      var commentCollection = options.collection,
          parentView = options.parentView,
          commentCollectionView = new List.CollectionView({
            collection : commentCollection
          });

      commentCollectionView.on('childview:comment:delete', function(childView, commentModel){
        commentModel.destroy({
          wait: true,
          success: function(){
            App.trigger('message:show', {
              status:'success',
              text: __('diamante_front.comment.controller.message.delete_success')
            });
          },
          error : function(model, xhr){
            App.alert({
              title: __('diamante_front.comment.controller.alert.delete_error.title'),
              xhr: xhr
            });
          }
        });
      });

      commentCollectionView.on('childview:comment:edit', function(childView, commentModel){
        require(['Comment/controllers/edit'], function(Edit){
          Edit.Controller(commentModel, childView, options);
        });
      });

      parentView.listRegion.show(commentCollectionView);

    };

  });

});