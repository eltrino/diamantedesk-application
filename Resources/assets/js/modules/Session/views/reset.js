define([
  'app',
  'config',
  'Common/views/form',
  'tpl!../templates/reset.ejs',
  'pwstrength'], function(App, Config, Form, loginTemplate){

  return App.module('Session', function(Session, App, Backbone, Marionette, $, _){

    Session.ResetView = Form.ItemView.extend({
      template: loginTemplate,
      className: 'auth-block reset-block',

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
