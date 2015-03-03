define([
  'app',
  './models/user',
  './controllers/user'], function(App){

  return App.module('User', function(User, App, Backbone, Marionette, $, _){


    App.on('user:render', function(options){
      User.Controller(options);
    });

  });

});