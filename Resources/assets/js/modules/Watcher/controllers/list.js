define(['app'], function(App){

  return App.module('Ticket.View.Watcher.List', function(List, App, Backbone, Marionette, $, _){

    List.Controller = function(options){

      var watcherCollection = options.collection,
          parentView = options.parentView,
          watcherCollectionView = new List.CollectionView({
            collection : watcherCollection
          });

      watcherCollection.fetch({
        success : function(){
          parentView.listRegion.show(watcherCollectionView);
        }
      });

    };

  });

});