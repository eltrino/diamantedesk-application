define(['app'], function(App){

  return App.module('SessionManager', function(SessionManager, App, Backbone, Marionette, $, _){

    SessionManager.Router = Marionette.AppRouter.extend({
      appRoutes: {
        'login' : 'login',
        'logout' : 'logout'
      }
    });

    var API = {
      login: function(){
        if(App.session.get('logged_in')){
          App.back({force:true});
        } else {
          require(['modules/SessionManager/controllers/login'], function(){
            SessionManager.LoginController();
          });
        }
      },
      logout: function(){
        App.session.logout();
        App.trigger('session:login');
      }
    };

    App.on('session:login', function(){
      App.debug('info', 'Event "session:login" fired');
      App.navigate('login', { nohistory:true });
      API.login();
    });

    App.on('session:logout', function(){
      App.debug('info', 'Event "session:logout" fired');
      App.navigate('logout', { nohistory:true });
      API.logout();
    });

    App.addInitializer(function(){
      new SessionManager.Router({
        controller: API
      });
    });

  });

});