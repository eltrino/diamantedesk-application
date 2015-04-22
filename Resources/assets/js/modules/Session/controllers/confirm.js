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

  });

});