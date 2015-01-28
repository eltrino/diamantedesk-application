define([
  'app',
  'config',
  'Common/views/form',
  'tpl!../templates/login.ejs'], function(App, Config, Form, loginTemplate){

  return App.module('SessionManager', function(SessionManager, App, Backbone, Marionette, $, _){

    SessionManager.LoginView = Form.ItemView.extend({
      template: loginTemplate,
      className: 'login-block',

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
        $('body').addClass('login-page');
      },

      onDestroy: function(){
        $('body').removeClass('login-page');
      }
    });

  });


});
