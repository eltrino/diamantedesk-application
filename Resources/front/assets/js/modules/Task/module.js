define(['app','./routers/task'], function(App){

  App.addInitializer(function(){
    if(!App.getCurrentRoute()){
      App.trigger('task:list');
    }
  });

});