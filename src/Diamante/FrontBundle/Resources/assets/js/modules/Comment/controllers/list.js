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
              text: 'Your comment was successfully deleted'
            });
          },
          error : function(model, xhr){
            App.alert({
              title: "Delete Comment Error",
              xhr: xhr
            });
          }
        });
      });

      commentCollectionView.on('childview:comment:edit', function(childView, commentModel){
        require(['Comment/controllers/edit'], function(Edit){
          Edit.Controller(commentModel, options);
        });
      });

      parentView.listRegion.show(commentCollectionView);

    };

  });

});