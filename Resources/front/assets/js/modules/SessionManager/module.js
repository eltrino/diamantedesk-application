define([
  'app',
  'backbone',
  './models/session',
  './routers/session'], function(App, Backbone, Model){

  App.addInitializer(function(){
    this.session = new Model.SessionModel();
    this.session.getAuth().fail(function(){
      this.trigger('session:login');
    }.bind(this));
  });

});