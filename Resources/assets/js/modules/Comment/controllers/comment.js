define(['app'], function(App){

  return App.module('Ticket.View.Comment', function(Comment, App, Backbone, Marionette, $, _){

    Comment.Controller = function(options){

      require([
        'Comment/models/comment',
        'Comment/controllers/list',
        'Comment/controllers/create',
        'Comment/views/layout'], function(Models, List, Create){

        var ticket = options.ticket,
            commentCollection = new Models.Collection(ticket.get('comments'), { ticket : ticket }),
            commentLayoutView = new Comment.LayoutView();

        options.parentRegion.show(commentLayoutView);

        List.Controller({
          ticket: ticket,
          parentView : commentLayoutView,
          collection : commentCollection
        });

        Create.Controller({
          ticket: ticket,
          parentView : commentLayoutView,
          collection : commentCollection
        });

      });

    };

  });

});