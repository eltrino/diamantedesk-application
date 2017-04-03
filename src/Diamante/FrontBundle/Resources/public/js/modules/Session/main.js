define([
  'app',
  'backbone',
  './models/session',
  './routers/session'], function(App, Backbone, Session){

  var routes = Session.Router.prototype.appRoutes;

  App.on('before:start',function(){
    Session.start();
    App.session = new Session.SessionModel();
    App.session.getAuth().done(function(){
      App.trigger('session:login:success');
    }).fail(function(){
      App.on('history:start', function(){
        var path = App.getCurrentRoute().replace(/\/.+?$/,'/:hash');
        if(!_.has(routes, path)){
          App.trigger('session:login', { return_path: App.getCurrentRoute() });
        }
      });
    });
  });

  App.on('session:login:success', function(){
    document.documentElement.classList.add('app-started');
    if(App.session.return_path){
      App.navigate(App.session.return_path, {trigger: true});
      delete App.session.return_path;
    }
  });

});