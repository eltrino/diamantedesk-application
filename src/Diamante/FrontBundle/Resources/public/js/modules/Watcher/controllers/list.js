define(['app'], function(App){

  return App.module('Ticket.View.Watcher.List', function(List, App, Backbone, Marionette, $, _){

    List.Controller = function(options){

      var watcherCollection = options.collection,
          parentView = options.parentView,
          watcherCollectionView = new List.CollectionView({
            collection : watcherCollection
          });

      watcherCollectionView.on('childview:watcher:delete', function(childView, watcherModel){
        watcherModel.destroy({
          wait: true,
          success : function(){
            App.trigger('message:show', {
              status:'success',
              text: __('diamante_front.watcher.controller.message.remove_success'),
            });
          },
          error : function(model, xhr){
            App.alert({
              title: __('diamante_front.watcher.controller.alert.delete_comment_error.title'),
              xhr: xhr
            });
          }
        });
      });

      watcherCollection.fetch({
        ticket: options.ticket,
        success : function(){
          parentView.listRegion.show(watcherCollectionView);
        }
      });

    };

  });

});