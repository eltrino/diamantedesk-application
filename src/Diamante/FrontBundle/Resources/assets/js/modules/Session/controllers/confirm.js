33333333333333333333333333333333333333333333333333333333define(['app'], function(App){

  return App.module('Session', function(Session, App, Backbone, Marionette, $, _){

    Session.ConfirmController = function(hash){

      App.session.confirm(hash)
        .done(function(){
          App.alert({
            title: __('diamante_front.session.controller.alert.confirm_success.title'),
            status: 'success',
            messages: [{
              status: 'success',
              text: __('diamante_front.session.controller.alert.confirm_success.text')
            }]
          });
          App.trigger('session:login');
        })
        .fail(function(model, xhr){
          App.alert({
            title: __('diamante_front.session.controller.alert.confirm_fail.title'),
            messages: [__('diamante_front.session.controller.alert.confirm_fail.text')]
          });
          App.trigger('session:registration');
        });

    };

    Session.ReConfirmController = function(email){

      App.session.reconfirm(email)
        .done(function(){
          App.alert({
            title: __('diamante_front.session.controller.alert.reconfirm_success.title'),
            status: 'success',
            messages: [{
              status: 'success',
              text: __('diamante_front.session.controller.alert.reconfirm_success.text', {email: email})
            }]
          });
          App.trigger('session:login');
        })
        .fail(function(model, xhr){
          App.alert({
            title: __('diamante_front.session.controller.alert.confirm_fail.title'),
            messages: [__('diamante_front.session.controller.alert.confirm_fail.text')]
          });
          App.trigger('session:registration');
        });

    };

  });

});