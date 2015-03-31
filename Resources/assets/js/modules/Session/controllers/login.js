define(['app'], function(App){

  return App.module('Session', function(Session, App, Backbone, Marionette, $, _){

    Session.LoginController = function(){

      require(['modules/Session/views/login'], function(){

        var loginView = new Session.LoginView({
          model : App.session
        });

        loginView.on('form:submit', function(data){
          this.model.login(data);
        });

        App.mainRegion.show(loginView);

      });

    };

  });

});