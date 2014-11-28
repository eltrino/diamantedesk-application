define(['app'], function(App){

  return App.module('SessionManager.Routers', function(Routers, App, Backbone, Marionette, $, _){

    Routers = Marionette.AppRouter.extend({
      appRoutes: {
        "login" : "login",
        "logout" : "logout",
        "register" : "register"
      }
    });

    var API = {
      login: function(){
        console.log('Login');
      },
      logout: function(){
        console.log('Logout');
      },
      register: function(){
        console.log('Register');
      }
    };

    App.addInitializer(function(){
      new Routers({
        controller: API
      });
    })

  });

});