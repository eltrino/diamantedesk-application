define([
  'app',
  'config',
  './views/header'], function(App, Config, HeaderView){

  return App.module('Header', function(Header, App, Backbone, Marionette, $, _) {

    Header.startWithParent = false;

    Header.on('start', function () {
      var headerView = new HeaderView.LayoutView(Config);
      Header.on('set:search', function(query){
        headerView.ui.searchInput.val(query);
      });
      App.trigger('user:render', { parentRegion: headerView.profileRegion });
      App.headerRegion.show(headerView);
    });

    App.on('session:login:success', function(){
      Header.start();
    });

  });

});