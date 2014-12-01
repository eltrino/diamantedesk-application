define(['app', '../common/wsse', 'config'], function(App, Wsse, Config) {

  return App.module('SessionManager', function(SessionManager, App, Backbone, Marionette, $, _){

    var username = "admin";
    var password = "1044d1dad87d990c3a5be102cafbf89cdff84738";

    window.Wsse = Wsse;

    //console.log(Wsse.getPasswordDigest( Wsse.getNonce().nonce, Wsse.getNonce().created, password));

    SessionManager.SessionModel = Backbone.Model.extend({

      initialize: function () {
        if(window.localStorage.getItem('authModel')){
          console.log(window.localStorage.getItem('authModel'));
        }
        this.set({ logged_in: true });
        this.set({ username: username });
        this.set({ password: password });
        this.addHeaders();
      },

      addHeaders: function(){
        $.ajaxPrefilter( function( options, originalOptions, jqXHR ) {
          if(this.get('logged_in')){
            jqXHR.setRequestHeader('Authorization', 'WSSE profile="UsernameToken"');
            jqXHR.setRequestHeader('X-WSSE', Wsse.getUsernameToken(this.get('username'), this.get('password')));
          }
        }.bind(this));
      },

      login: function(creds) {
        this.set(creds);
        this.set({ logged_in: true });
        window.localStorage.setItem('authModel', JSON.stringify(this));
      },

      logout: function() {
        this.clear();
        this.set({ logged_in: false });
        window.localStorage.removeItem('authModel');
      },

      getAuth: function() {
        var defer = $.Deferred();
        if(this.get('logged_in')){
          $.get(Config.apiUrl + '/user/filter.json', {username : this.get('username')}, {
            success: function(){
              defer.resolve();
            },
            error: function(){
              defer.reject();
            }
          });
        } else {
          defer.reject();
        }
        return defer.promise();
      }

    });

  });

});