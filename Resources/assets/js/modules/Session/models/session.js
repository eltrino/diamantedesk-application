define([
  'app',
  'User/models/user',
  '../common/wsse'], function(App, User, Wsse) {

  return App.module('Session', function(Session, App, Backbone, Marionette, $, _){

    var username = 'admin';
    var password = '5c76179545225078a3ba580dff644b0113faf9dc';

    Session.startWithParent = false;


    Session.SessionModel = Backbone.Model.extend({

      initialize: function () {
        var savedData = window.localStorage.getItem('authModel') || window.sessionStorage.getItem('authModel');
        if(savedData){
          this.set(JSON.parse(savedData));
        }
        this.addHeaders();
        $.ajaxSetup({
          statusCode: {
            401: function () {
              if(App.getCurrentRoute() !== 'login'){
                this.logout();
                App.alert({ title: "Authorization Required", messages: ["this action require authorization"] });
                App.trigger('session:login');
              }
            }.bind(this)
          }
        });
      },

      validate: function(attrs, options){
        var errors = {};
        if(!attrs.username) {
          errors.username = "login is required";
        }
        if(!attrs.password) {
          errors.password = "password is required";
        }
        if(!_.isEmpty(errors)){
          return errors;
        }
      },

      addHeaders: function(){
        $.ajaxPrefilter( function( options, originalOptions, jqXHR ) {
          if(this.get('username') && this.get('password')){
            jqXHR.setRequestHeader('Authorization', 'WSSE profile="UsernameToken"');
            jqXHR.setRequestHeader('X-WSSE', Wsse.getUsernameToken(this.get('username'), this.get('password')));
          }
        }.bind(this));
      },

      loginSuccess: function(data) {
        this.set({
          id: data.id,
          logged_in: true
        });
        this.trigger('login:success');
        if(this.get('remember')){
          window.localStorage.setItem('authModel', JSON.stringify(this));
        } else {
          window.sessionStorage.setItem('authModel', JSON.stringify(this));
        }
        App.trigger('session:login:success');
      },

      loginFail: function(){
        this.trigger('login:fail');
        this.clear();
        this.set({ logged_in: false });
        App.trigger('session:login:fail');
        App.alert({ title: "Authorization Failed", messages: ["Username or password is wrong"] });
      },

      login: function(creds) {
        creds.password = Wsse.encodePassword(creds.password);
        if(this.set(creds, {validate: true})){
          this.getAuth().done(this.loginSuccess.bind(this)).fail(this.loginFail.bind(this));
        }
      },

      logout: function() {
        this.clear();
        this.set({ logged_in: false });
        window.localStorage.removeItem('authModel');
        window.sessionStorage.removeItem('authModel');
        App.trigger('session:logout:success');
      },

      getAuth: function() {
        var defer = $.Deferred();
        if(this.get('username') && this.get('password')){
          return App.request('user:model:current');
        } else {
          defer.reject();
        }
        return defer.promise();
      }

    });



  });

});