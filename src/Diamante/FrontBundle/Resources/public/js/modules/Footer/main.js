define([
  'app',
  'config',
  './views/footer'], function(App, Config, FooterView){

  return App.module('Footer', function(Footer, App, Backbone, Marionette, $, _) {

    Footer.startWithParent = false;

    Footer.on('start', function () {
      var footerView = new FooterView.View(Config);
      App.footerRegion.show(footerView);
    });

    App.on('session:login:success', function(){
      Footer.start();
    });

  });

});