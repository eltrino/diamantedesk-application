define(['app'], function(App){

  return App.module('Ticket.View.Watcher', function(Watcher, App, Backbone, Marionette, $, _){

    Watcher.Controller = function(options){

      require([
        'Watcher/models/watcher',
        'Watcher/controllers/list',
        'Watcher/controllers/add',
        'Watcher/views/layout'], function(Models, List, Add){

        var ticket = options.ticket,
            watcherCollection = new Models.Collection([], { ticket : ticket }),
            watcherLayoutView = new Watcher.LayoutView();

        options.parentRegion.show(watcherLayoutView);

        List.Controller({
          ticket: ticket,
          parentView : watcherLayoutView,
          collection : watcherCollection
        });

        watcherLayoutView.on('watcher:add', function(){
          Add.Controller({
            ticket: ticket,
            collection : watcherCollection
          });
        });

      });

    };

  });

});