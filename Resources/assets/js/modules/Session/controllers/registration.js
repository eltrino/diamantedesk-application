define(['app'], function(App){

  return App.module('Session', function(Session, App, Backbone, Marionette, $, _){

    Session.RegistrationController = function(){

      require(['modules/Session/views/registration'], function(){

        var registrationView = new Session.RegistrationView({
          model : App.session
        });

        registrationView.on('form:submit', function(data){
          this.model.register(data);
          this.trigger('session:login');
        });

        App.mainRegion.show(registrationView);

      });

    };

  });

});