define(['modules/Task/routers/task'], function(){

  App.addInitializer(function(){
    Backbone.history.start();
    if(App.getCurrentRoute() == ""){
      App.trigger('task:list');
    }
  });

});