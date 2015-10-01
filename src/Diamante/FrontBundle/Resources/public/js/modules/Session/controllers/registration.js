define(['app'], function(App){

  return App.module('Session', function(Session, App, Backbone, Marionette, $, _){

    Session.RegistrationController = function(){

      require(['modules/Session/views/registration'], function(){

        var registrationView = new Session.RegistrationView({
          model : App.session
        });

        registrationView.on('form:submit', function(data){
          this.model.register(data).
            done(function(model){
              App.alert({ title: 'Registration Success', messages: [{
                status: 'success',
                text: 'Thank you. <br>' +
                'We have sent you email to ' + model.get('email') + '.<br>'+
                'Please click the link in that message to activate your account.'
              }] });
              App.trigger('session:login');
            }).
            fail(function(model, xhr){
              App.alert({ title: "Registration Failed", xhr: xhr });
            });
        });

        App.mainRegion.show(registrationView);

      });

    };

  });

});