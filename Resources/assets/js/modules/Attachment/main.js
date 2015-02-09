define(['app'], function(App){

  return App.module('Ticket.View.Attachment', function(Attachment, App, Backbone, Marionette, $, _){

    Attachment.startWithParent = false;

    Attachment.on('start', function(options){
      Attachment.render(options);
      Attachment.ready = true;
    });

    Attachment.render = function(options){
      require(['Attachment/controllers/attachment'], function(Attachment){
        Attachment.Controller(options);
      });
    };

  });

});