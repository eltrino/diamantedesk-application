define(['app'], function(App){

  return App.module('SessionManager.Routers', function(Routers, App, Backbone, Marionette, $, _){

    Routers = Marionette.AppRouter.extend({
      appRoutes: {
        "login" : "login",
        "logout" : "logout"
      }
    });

    var API = {
      login: function(){

      },
      logout: function(){
        App.session.logout();
      }
    };

    App.addInitializer(function(){
      new Routers({
        controller: API
      });
    });

  });

});