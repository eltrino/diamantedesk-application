define([
  'app',
  'config',
  './views/header'], function(App, Config, HeaderView){

  return App.module('Header', function(Header, App, Backbone, Marionette, $, _) {

    Header.startWithParent = false;

    Header.on('start', function () {
      var View = new HeaderView.View(Config);
      Header.on('set:search', function(query){
        View.ui.searchInput.val(query);
      });
      App.headerRegion.show(View);
    });

    App.on('session:login:success', function(){
      Header.start();
    });

  });

});