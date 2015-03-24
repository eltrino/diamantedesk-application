define(['app'], function(App){

  App.on('message:show', function(messages){
    require(['Common/views/message'], function(Message){
      App.messagesRegion.show(new Message.View(messages));
    });
  });

});