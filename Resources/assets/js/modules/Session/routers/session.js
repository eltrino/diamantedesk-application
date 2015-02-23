define(['app'], function(App){

  return App.module('Session', function(Session, App, Backbone, Marionette, $, _){

    Session.startWithParent = false;

    Session.Router = Marionette.AppRouter.extend({
      appRoutes: {
        'login' : 'login',
        'logout' : 'logout',
        'registration': 'registration',
        'confirm/:activation_hash': 'confirm',
        'resetpassword': 'reset'
      }
    });

    var API = {
      login: function(){
        if(App.session.get('logged_in')){
          App.back({force:true});
        } else {
          require(['modules/Session/controllers/login'], function(){
            Session.LoginController();
          });
        }
      },

      logout: function(){
        App.session.logout();
        App.trigger('session:login');
      },

      registration: function(){
        if(App.session.get('logged_in')){
          App.back({force:true});
        } else {
          require(['modules/Session/controllers/registration'], function(){
            Session.RegistrationController();
          });
        }
      },

      confirm: function(activation_hash){
        App.session.confirm(activation_hash);
      },

      reset: function(){
        if(App.session.get('logged_in')){
          App.back({force:true});
        } else {
          require(['modules/Session/controllers/reset'], function(){
            Session.ResetController();
          });
        }
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

    App.on('session:registration', function(){
      App.debug('info', 'Event "session:registration" fired');
      App.navigate('registration');
      API.registration();
    });

    App.on('session:reset', function(){
      App.debug('info', 'Event "session:reset" fired');
      App.navigate('resetpassword');
      API.reset();
    });

    Session.on('start',function(){
      new Session.Router({
        controller: API
      });
    });

  });

});