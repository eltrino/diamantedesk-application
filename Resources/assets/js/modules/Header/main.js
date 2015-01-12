define(['app','./views/header'], function(App, HeaderView){

  return App.module('Header', function(Header, App, Backbone, Marionette, $, _) {

    Header.startWithParent = false;

    Header.on('start', function () {
      var View = new HeaderView.View();
      Header.on('set:search', function(query){
        View.ui.searchInput.val(query);
      });
      App.HeaderRegion.show(View);
    });

    App.on('session:login:success', function(){
      Header.start();
    });

  });

});