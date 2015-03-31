define([
  'app',
  'config',
  'User/models/user',
  'helpers/wsse'], function(App, Config, User, Wsse) {

  function getCookie(name) {
    var matches = document.cookie.match(new RegExp(
      "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
  }
  function setCookie(name, value, options) {
    options = options || {};
    var expires = options.expires;
    if (typeof expires == "number" && expires) {
      var d = new Date();
      d.setTime(d.getTime() + expires*1000);
      expires = options.expires = d;
    }
    if (expires && expires.toUTCString) {
      options.expires = expires.toUTCString();
    }
    value = encodeURIComponent(value);
    var updatedCookie = name + "=" + value;
    for(var propName in options) {
      updatedCookie += "; " + propName;
      var propValue = options[propName];
      if (propValue !== true) {
        updatedCookie += "=" + propValue;
      }
    }
    document.cookie = updatedCookie;
  }
  function deleteCookie(name) {
    setCookie(name, "", { expires: -1 });
  }


  return App.module('Session', function(Session, App, Backbone, Marionette, $, _){

    Session.startWithParent = false;


    Session.SessionModel = Backbone.Model.extend({

      url: Config.apiUrl.replace('api/diamante/rest/latest', 'diamantefront') + '/user',

      initialize: function(){
        var savedData = window.localStorage.getItem('authModel') || getCookie('authModel');
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
                App.trigger('session:login', { return_path: App.getCurrentRoute() });
              }
            }.bind(this)
          }
        });
      },

      validate: function(attrs, options){
        var errors = {};
        if(_.indexOf(options.ignore, 'email') === -1){
          if(!attrs.email) {
            errors.email = "Can't be blank";
          }
        }
        if(_.indexOf(options.ignore, 'password') === -1){
          if(!attrs.password) {
            errors.password = "Can't be blank";
          } else if(attrs.password.length < 6) {
            errors.password = 'Must be at least six (6) symbols';
          }
        }
        if(!_.isEmpty(errors)){
          return errors;
        }
      },

      addHeaders: function(){
        $.ajaxPrefilter( function( options, originalOptions, jqXHR ) {
          if(this.get('email') && this.get('password')){
            jqXHR.setRequestHeader('Authorization', 'WSSE profile="UsernameToken"');
            jqXHR.setRequestHeader('X-WSSE', Wsse.getUsernameToken(this.get('email'), this.get('password')));
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
          setCookie('authModel', JSON.stringify(this));
        }
        App.trigger('session:login:success');
      },

      loginFail: function(){
        this.trigger('login:fail');
        this.clear();
        this.set({ logged_in: false });
        App.trigger('session:login:fail');
        App.alert({ title: "Authorization Failed", messages: ["Incorrect email or password"] });
      },

      login: function(creds) {
        if(creds.password){
          creds.password = Wsse.encodePassword(creds.password);
        }
        if(this.set(creds, {validate: true})){
          this.getAuth().done(this.loginSuccess.bind(this)).fail(this.loginFail.bind(this));
        }
      },

      register: function(creds) {
        if(creds.password){
          creds.password = Wsse.encodePassword(creds.password);
        }
        this.save(creds,{
          success : function(){
            App.alert({ title: 'Registration Success', messages: [{
              status: 'success',
              text: 'Thank you. <br>' +
                'We have sent you email to ' + this.get('email') + '.<br>'+
                'Please click the link in that message to activate your account.'
            }] });
            this.clear();
            App.trigger('session:register:success');
            App.trigger('session:login');
          }.bind(this),
          error : function(){
            App.trigger('session:register:fail');
            App.alert({ title: "Registration Failed" });
          }
        });
      },

      confirm: function(hash){
        var model = this;
        this.url += '/confirm';
        this.set('id', 1);
        this.save({ hash : hash },{
          patch: true,
          validate: false,
          success : function(){
            App.trigger('session:confirm:success');
            App.alert({ title: 'Email Confirmation Success', messages: [{
              status:'success',
              text: 'You may login and use application'}] });
            App.trigger('session:login');
          },
          error : function(){
            App.trigger('session:confirm:fail');
            App.alert({ title: 'Email Confirmation Failed', messages: ['Activation code is wrong'] });
            App.trigger('session:registration');
          },
          complete : function(){
            model.url = model.url.replace('/confirm', '');
            model.clear();
          }
        });
      },

      reset: function(data){
        var model = this;
        this.url += '/reset';
        this.set('id', 1);
        this.save(data, {
          patch: true,
          ignore: ['password'],
          success : function(){
            App.trigger('session:reset:sent');
            App.alert({ title: 'Password Reset Info', messages: [{
              status:'info',
              text: 'We have sent you email to ' + model.get('email') + '.<br>' +
                'Please click the link in that message to reset your password.'
            }] });
            App.trigger('session:login');
          },
          error : function(){
            App.trigger('session:reset:fail');
            App.alert({ title: 'Password Reset Failed', messages: ['Email not found'] });
          },
          complete : function(){
            model.url = model.url.replace('/reset', '');
            model.clear();
          }
        });
        if(!this.isValid()){
          this.url = this.url.replace('/reset', '');
        }
      },

      newPassword: function(data){
        var model = this;
        this.url += '/password';
        this.set('id', 1);
        data.password = Wsse.encodePassword(data.password);
        this.save(data, {
          patch: true,
          ignore: ['email'],
          success : function(){
            App.trigger('session:reset:success');
            App.alert({ title: 'Password Reset Success', messages: [{
              status:'success',
              text: 'Password successfully changed, you can use it to login'}] });
            App.trigger('session:password:change');
          },
          error : function(){
            App.trigger('session:reset:fail');
            App.alert({ title: 'Password Reset Failed', messages: ['Reset Code is invalid or expired'] });
            model.clear();
            App.trigger('session:reset');
          },
          complete : function(){
            model.url = model.url.replace('/password', '');
            model.clear();
          }
        });
        if(!this.isValid()){
          this.url = this.url.replace('/password', '');
        }
      },

      logout: function() {
        this.clear();
        this.set({ logged_in: false });
        window.localStorage.removeItem('authModel');
        deleteCookie('authModel');
        App.trigger('session:logout:success');
      },

      getAuth: function() {
        var defer = $.Deferred();
        if(this.get('email') && this.get('password')){
          return App.request('user:model:current');
        } else {
          defer.reject();
        }
        return defer.promise();
      }

    });



  });

});