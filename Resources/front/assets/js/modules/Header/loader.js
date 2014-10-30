define(['modules/Header/views/header'], function(){

  App.addInitializer(function(){
    App.HeaderRegion.show(new App.Header.View())
  });

});