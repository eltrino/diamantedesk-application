define(['app', 'User/models/user', '../common/wsse', 'config'], function(App, User, Wsse, Config) {

  return App.module('SessionManager', function(SessionManager, App, Backbone, Marionette, $, _){

    var username = "admin";
    var password = "ff561f86ef7cea426ebd1c0d57ef00f69cebcf2c";

    function getCookie(name) {
      var matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
      return matches ? decodeURIComponent(matches[1]) : undefined;
    }



    SessionManager.SessionModel = Backbone.Model.extend({

      initialize: function () {
        var savedData = window.localStorage.getItem('authModel') || window.sessionStorage.getItem('authModel');
        if(savedData){
          this.set(JSON.parse(savedData));
          this.addHeaders();
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

      login: function(creds) {
        this.set(creds);
        this.addHeaders();
        this.getAuth().done(function(){
          this.trigger('login:success');
          this.set({ logged_in: true });
          if(creds.remember){
            window.localStorage.setItem('authModel', JSON.stringify(this));
          } else {
            window.sessionStorage.setItem('authModel', JSON.stringify(this));
          }
          App.trigger('session:login:success');
        }.bind(this)).fail(function(){
          this.trigger('login:fail');
          this.clear();
          this.set({ logged_in: false });
          App.trigger('session:login:fail');
        }.bind(this));

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

          App.request("user:model:current")
            .done(function(){defer.resolve();})
            .fail(function(){defer.reject();});

        } else {
          defer.reject();
        }
        return defer.promise();
      }

    });



  });

});