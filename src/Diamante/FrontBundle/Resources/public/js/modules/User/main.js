define([
  'app',
  './routers/user',
  './models/user',
  './controllers/user'], function(App){

  return App.module('User', function(User, App, Backbone, Marionette, $, _){

    App.on('session:login:success', function(){
      User.start();
    });

    App.on('user:render', function(options){
      User.Controller(options);
    });

  });

});
