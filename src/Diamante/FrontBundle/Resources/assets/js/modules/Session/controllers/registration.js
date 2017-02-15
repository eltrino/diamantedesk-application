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
              App.alert({
                title: __('diamante_front.session.controller.alert.register_success.title'),
                status: 'success',
                messages: [{
                  status: 'success',
                  text: __('diamante_front.session.controller.alert.register_success.text', {email: model.get(email)})
                }]
              });
              App.trigger('session:login');
            }).
            fail(function(model, xhr){
              App.alert({
                title: __('diamante_front.session.controller.alert.register_fail.title'),
                xhr: xhr
              });
            });
        });

        App.mainRegion.show(registrationView);

      });

    };

  });

});