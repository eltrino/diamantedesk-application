define([
  'app',
  'config',
  './views/header'], function(App, Config, HeaderView){

  return App.module('Header', function(Header, App, Backbone, Marionette, $, _) {

    Header.startWithParent = false;

    Header.on('start', function () {
      var headerView = new HeaderView.LayoutView(Config);
      App.trigger('user:render', { parentRegion: headerView.profileRegion });
      App.headerRegion.show(headerView);
      Header.on('set:search', function(search){
        headerView.ui.searchInput.val(search);
      });
    });

    Header.on('stop', function () {
      Header.off('set:search');
    });

    App.on('session:login:success', function(){
      Header.start();
    });

    App.on('session:logout:success', function(){
      Header.stop();
    });

  });

});