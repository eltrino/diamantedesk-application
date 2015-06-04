define(['app'], function(App){

  return App.module('Session', function(Session, App, Backbone, Marionette, $, _){

    Session.ConfirmController = function(hash){

      App.session.confirm(hash)
        .done(function(){
          App.alert({ title: 'Email Confirmation Success', messages: [{
            status: 'success',
            text: 'You may login and use application'}] });
          App.trigger('session:login');
        })
        .fail(function(model, xhr){
          App.alert({ title: 'Email Confirmation Failed', messages: ['Activation code is wrong'] });
          App.trigger('session:registration');
        });

    };

    Session.ReConfirmController = function(email){

      App.session.reconfirm(email)
        .done(function(){
          App.alert({ title: 'Email Confirmation Success', messages: [{
            status: 'success',
            text:
              'We have sent you email to ' + email + '.<br>'+
              'Please click the link in that message to activate your account.'
          }] });
          App.trigger('session:login');
        })
        .fail(function(model, xhr){
          App.alert({ title: 'Email Confirmation Failed', messages: ['Activation code is wrong'] });
          App.trigger('session:registration');
        });

    };

  });

});