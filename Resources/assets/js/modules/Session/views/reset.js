define([
  'app',
  'config',
  'Common/views/form',
  'tpl!../templates/reset.ejs'], function(App, Config, Form, loginTemplate){

  return App.module('Session', function(Session, App, Backbone, Marionette, $, _){

    Session.ResetView = Form.ItemView.extend({
      template: loginTemplate,
      className: 'auth-block reset-block',

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
        'reset:success' : 'resetSuccess',
        'invalid' : 'formDataInvalid'
      },

      resetSuccess: function(){
        this.$el.fadeOut();
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
