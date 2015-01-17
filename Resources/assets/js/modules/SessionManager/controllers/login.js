define(['app'], function(App){

  return App.module('SessionManager', function(SessionManager, App, Backbone, Marionette, $, _){

    SessionManager.LoginController = function(){

      require(['modules/SessionManager/views/login'], function(){

        var loginView = new SessionManager.LoginView({
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