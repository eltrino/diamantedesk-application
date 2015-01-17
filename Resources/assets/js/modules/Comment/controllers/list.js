define(['app'], function(App){

  return App.module('Ticket.View.Comment.List', function(List, App, Backbone, Marionette, $, _){

    List.Controller = function(options){

      require([
        'Comment/models/comment',
        'Comment/controllers/form',
        'Comment/views/list'], function(Models, Form){

        var ticket = options.ticket,
            commentCollection = new Models.Collection(ticket.get('comments')),
            commentCollectionView = new List.CollectionView({
              collection : commentCollection
            }),
            commentLayoutView = new List.LayoutView({});

        options.parentRegion.show(commentLayoutView);
        commentLayoutView.listRegion.show(commentCollectionView);

        Form.Controller({
          ticket: ticket,
          parentRegion : commentLayoutView.formRegion,
          collection : commentCollection
        });

      });

    };

  });

});