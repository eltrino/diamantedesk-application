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

  function validateEmail(email) {
    return !!String(email).match(/^\s*[\w\-\+_]+(?:\.[\w\-\+_]+)*@[\w\-\+_]+\.[\w\-\+_]+(?:\.[\w‌​\-\+_]+)*\s*$/);
  }


  return App.module('Session', function(Session, App, Backbone, Marionette, $, _){

    var trim = $.trim;

    Session.startWithParent = false;


    Session.SessionModel = Backbone.Model.extend({

      url: Config.apiUrl.replace('api/diamante/rest/latest', 'diamantefront') + '/user',

      initialize: function(){
        var savedData = window.localStorage.getItem('authModel') || getCookie('authModel');
        if(savedData){
          this.set(JSON.parse(savedData));
        }
        this.addHeaders();
        $(document).ajaxError(function(event, jqxhr, settings){
          if(jqxhr.status === 401 && App.getCurrentRoute() !== 'login') {
            this.logout();
            App.alert({ title: "Authorization Required", messages: ["This action require authorization"] });
            App.trigger('session:login', { return_path: App.getCurrentRoute() });
          }
        }.bind(this));
      },

      validate: function(attrs, options){
        var errors = {};
        if(_.indexOf(options.ignore, 'email') === -1){
          if(!validateEmail(attrs.email)){
            errors.email = '"' + attrs.email + '" is not a valid email';
          }
          if(!trim(attrs.email)) {
            errors.email = 'Can\'t be blank';
          }
        }
        if(_.indexOf(options.ignore, 'password') === -1){
          if(!trim(attrs.password)) {
            errors.password = 'Can\'t be blank';
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
        if(this.get('remember')){
          window.localStorage.setItem('authModel', JSON.stringify(this));
        } else {
          setCookie('authModel', JSON.stringify(this));
        }
        App.trigger('session:login:success');
      },

      loginFail: function(data, xhr){
        this.clear();
        this.set({ logged_in: false });
        this.trigger('error');
        App.trigger('session:login:fail');
      },

      login: function(creds) {
        var model = this,
            defer = $.Deferred();
        if(creds.password){
          creds.password = Wsse.encodePassword(creds.password);
        }
        if(this.set(creds, {validate: true})){
          this.getAuth().done(function(data){
            model.loginSuccess(data);
            defer.resolve(data);
          }).fail(function(data, xhr){
            model.loginFail(data, xhr);
            defer.reject(data, xhr);
          });
        }
        return defer.promise();
      },

      register: function(creds) {
        var defer = $.Deferred();
        if(this.set(creds, {validate: true})){
          creds.password = Wsse.encodePassword(creds.password);
          this.save(creds,{
            success : function(model){
              defer.resolve(model);
              model.clear();
            },
            error : function(model, xhr){
              defer.reject(model, xhr);
            }
          });
        }
        return defer.promise();
      },

      confirm: function(hash){
        var model = this,
            defer = $.Deferred();
        this.url += '/confirm';
        this.set('id', 1);
        this.save({ hash : hash },{
          patch: true,
          validate: false,
          success : function(){
            defer.resolve(model);
          },
          error : function(model, xhr){
            defer.reject(model, xhr);
          },
          complete : function(){
            model.url = model.url.replace('/confirm', '');
            model.clear();
          }
        });
        return defer.promise();
      },

      reconfirm: function(email){
        var model = this,
            defer = $.Deferred();
        this.url += '/sendConfirmation';
        this.set('id', 1);
        this.save({ email : email },{
          patch: true,
          validate: false,
          success : function(){
            defer.resolve(model);
          },
          error : function(model, xhr){
            defer.reject(model, xhr);
          },
          complete : function(){
            model.url = model.url.replace('/sendConfirmation', '');
            model.clear();
          }
        });
        return defer.promise();
      },

      reset: function(data){
        var model = this,
            defer = $.Deferred(),
            isValid = false;
        this.url += '/reset';
        this.set('id', 1);
        isValid = this.save(data, {
          patch: true,
          ignore: ['password'],
          success : function(){
            defer.resolve(model);
          },
          error : function(model, xhr){
            defer.reject(model, xhr);
          },
          complete : function(){
            model.url = model.url.replace('/reset', '');
            model.clear();
          }
        });
        if(!isValid){
          this.url = this.url.replace('/reset', '');
        }
        return defer.promise();
      },

      newPassword: function(data){
        var model = this,
            defer = $.Deferred();
        this.url += '/password';
        this.set('id', 1);
        if(this.set(data, {validate: true, ignore: ['email']})){
          data.password = Wsse.encodePassword(data.password);
          this.save(data, {
            patch: true,
            ignore: ['email'],
            success : function(){
              defer.resolve(model);
            },
            error : function(){
              model.clear();
              defer.reject(model, xhr);
            },
            complete : function(){
              model.url = model.url.replace('/password', '');
              model.clear();
            }
          });
        } else {
          this.url = this.url.replace('/password', '');
        }
        return defer.promise();
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
      },

      update: function(attr){
        this.set(attr);
        if(window.localStorage.getItem('authModel')){
          window.localStorage.setItem('authModel', JSON.stringify(this));
        } else {
          setCookie('authModel', JSON.stringify(this));
        }
      }

    });

  });

});