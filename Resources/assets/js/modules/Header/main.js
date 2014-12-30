define(['app','./views/header'], function(App, HeaderView){

  return App.module('Header', function(Header, App, Backbone, Marionette, $, _) {

    Header.startWithParent = false;

    Header.on('start', function () {
      App.HeaderRegion.show(new HeaderView.View());
    });

    App.on('session:login:success', function(){
      Header.start();
    });

  });

});