define(['app'], function(App){

  return App.module('Ticket.View.Comment.List', function(List, App, Backbone, Marionette, $, _){

    List.Controller = function(options){

      require(['Comment/models/comment', 'Comment/controllers/form' , 'Comment/views/list'], function(Models, Form){

        var Ticket = options.ticket,
            CommentCollection = new Models.Collection(Ticket.get('comments')),
            CommentCollectionView = new List.CollectionView({
              collection : CommentCollection
            }),
            CommentLayoutView = new List.LayoutView({});

        options.parentRegion.show(CommentLayoutView);
        CommentLayoutView.ListRegion.show(CommentCollectionView);

        Form.Controller({
          ticket: Ticket,
          parentRegion : CommentLayoutView.FormRegion,
          collection : CommentCollection
        });

      });

    };

  });

});