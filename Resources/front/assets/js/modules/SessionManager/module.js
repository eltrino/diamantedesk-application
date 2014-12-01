define([
  'app',
  './models/session',
  './routers/session'], function(App, Model){

  App.addInitializer(function(){
    App.session = new Model.SessionModel();
    App.session.getAuth();
  });

  window.App = App;

});