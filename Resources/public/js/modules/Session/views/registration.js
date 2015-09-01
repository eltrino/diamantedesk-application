define([
  'app',
  'config',
  'Common/views/form',
  'tpl!../templates/registration.ejs',
  'pwstrength'], function(App, Config, Form, registrationTemplate){

  return App.module('Session', function(Session, App, Backbone, Marionette, $, _){

    Session.RegistrationView = Form.ItemView.extend({
      template: registrationTemplate,
      className: 'auth-block registration-block',

      initialize: function(){
        this.baseUrl = Config.baseUrl;
        this.basePath = Config.basePath;
      },

      templateHelpers: function(){
        return {
          baseUrl: this.baseUrl,
          basePath: this.basePath
        };
      },

      onShow: function(){
        $('body').addClass('auth-page');
        this.$(':password').pwstrength();
      },

      onDestroy: function(){
        $('body').removeClass('auth-page');
      }
    });

  });


});
