define(['app'], function(App){

  return App.module('Ticket.View.Attachment', function(Watcher, App, Backbone, Marionette, $, _){

    Watcher.Controller = function(options){

      require([
        'Watcher/models/watcher',
        'Watcher/controllers/list',
        'Watcher/views/layout'], function(Models, List){

        var ticket = options.ticket,
            watcherCollection = new Models.Collection([], { ticket : ticket }),
            watcherLayoutView = new Watcher.LayoutView();

        options.parentRegion.show(watcherLayoutView);

        List.Controller({
          ticket: ticket,
          parentView : watcherLayoutView,
          collection : watcherCollection
        });

      });

    };

  });

});