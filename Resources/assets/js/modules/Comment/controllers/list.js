define(['app'], function(App){

  return App.module('Ticket.View.Comment.List', function(List, App, Backbone, Marionette, $, _){

    List.Controller = function(options){

      require([
        'Comment/models/comment',
        'Comment/controllers/form',
        'Comment/views/list'], function(Models, Form){

        var ticket = options.ticket,
            commentCollection = new Models.Collection(ticket.get('comments'), { ticket : options.ticket }),
            commentCollectionView = new List.CollectionView({
              collection : commentCollection
            }),
            commentLayoutView = new List.LayoutView();

        commentCollectionView.on('childview:comment:delete', function(childView, commentModel){
          commentModel.destroy({
            wait: true,
            error : function(model, xhr){
              App.alert({
                title: "Delete Comment Error",
                xhr: xhr
              });
            }
          });
        });

        commentCollectionView.on('childview:comment:edit', function(childView, commentModel){
          Form.Controller(commentModel, {
            parentRegion: commentLayoutView.formRegion
          });
        });

        options.parentRegion.show(commentLayoutView);
        commentLayoutView.listRegion.show(commentCollectionView);

        Form.Controller(null, {
          ticket: ticket,
          parentRegion : commentLayoutView.formRegion,
          collection : commentCollection
        });

      });

    };

  });

});