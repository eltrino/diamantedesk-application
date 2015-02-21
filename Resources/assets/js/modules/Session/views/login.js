define([
  'app',
  'config',
  'Common/views/form',
  'tpl!../templates/login.ejs'], function(App, Config, Form, loginTemplate){

  return App.module('Session', function(Session, App, Backbone, Marionette, $, _){

    Session.LoginView = Form.ItemView.extend({
      template: loginTemplate,
      className: 'auth-block login-block',

      initialize: function(){
        this.baseUrl = Config.baseUrl;
        this.basePath = Config.basePath;
      },

      serializeData: function(){
        return {
          baseUrl: this.baseUrl,
          basePath: this.basePath
        };
      },

      modelEvents: {
        'login:success' : 'loginSuccess',
        'login:fail' : 'loginFail',
        'invalid' : 'formDataInvalid'
      },

      loginSuccess: function(){
        this.$el.fadeOut();
      },

      loginFail: function(){

      },

      onShow: function(){
        $('body').addClass('auth-page');
      },

      onDestroy: function(){
        $('body').removeClass('auth-page');
      }
    });

  });


});
