define(['app', '../common/wsse', 'config'], function(App, Wsse, Config) {

  return App.module('SessionManager', function(SessionManager, App, Backbone, Marionette, $, _){

    var username = "admin";
    var password = "1044d1dad87d990c3a5be102cafbf89cdff84738";


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
          $.get(Config.apiUrl + '/user/filter.json', { username : this.get('username') })
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