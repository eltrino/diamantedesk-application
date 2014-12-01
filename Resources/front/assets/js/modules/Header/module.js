define(['app','./views/header'], function(App, HeaderView){

  App.addInitializer(function(){
    App.HeaderRegion.show(new HeaderView.View());
  });

});