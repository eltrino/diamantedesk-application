define([
  'app',
  'config',
  'tpl!../templates/login.ejs'], function(App, Config, loginTemplate){

  return App.module('SessionManager', function(SessionManager, App, Backbone, Marionette, $, _){

    SessionManager.LoginView = Marionette.ItemView.extend({
      template: loginTemplate,
      className: 'login-block',

      initialize: function(){
        this.baseUrl = Config.baseUrl;
      },

      serializeData: function(){
        return {
          baseUrl: this.baseUrl
        };
      },

      events: {
        "click .js-submit" : "submitForm"
      },

      submitForm: function(e){
        e.preventDefault();
        var arr = this.$('form').serializeArray(), i = arr.length, data = {};
        for(;i--;) {
          data[arr[i].name] = arr[i].value;
        }
        this.trigger('form:submit', data);
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
