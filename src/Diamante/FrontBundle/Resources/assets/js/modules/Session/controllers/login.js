define(['app'], function(App){

  return App.module('Session', function(Session, App, Backbone, Marionette, $, _){

    Session.LoginController = function(){

      require(['modules/Session/views/login'], function(){

        var loginView = new Session.LoginView({
          model : App.session
        });

        loginView.on('form:submit', function(data){
          this.model.login(data).fail(function(model, xhr){
            App.alert({
              title: __('diamante_front.session.controller.alert.login_fail.title'),
              xhr: xhr
            });
          });
        });

        App.mainRegion.show(loginView);

      });

    };

  });

});