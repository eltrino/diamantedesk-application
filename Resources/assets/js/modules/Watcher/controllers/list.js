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
          error : function(model, xhr){
            App.alert({
              title: "Delete Comment Error",
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