define(['app'], function(App){

  return App.module('Ticket.View.Comment', function(Comment, App, Backbone, Marionette, $, _){

    Comment.startWithParent = false;

    Comment.on('start', function(options){
      Comment.render(options);
      Comment.ready = true;
    });

    Comment.render = function(options){
      require(['Comment/controllers/comment'], function(Comment){
        Comment.Controller(options);
      });
    };

  });

});