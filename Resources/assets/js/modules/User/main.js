define([
  'app',
  './models/user',
  './controllers/user'], function(App){

  return App.module('User', function(User, App, Backbone, Marionette, $, _){

    App.on('user:render', function(options){
      User.Controller(options);
    });

    App.on('user:view', function(options){
      require(['User/controllers/view'], function(View){
        View.Controller(options);
      });
    });

    App.on('user:edit', function(options){
      require(['User/controllers/edit'], function(Edit){
        Edit.Controller(options);
      });
    });

  });

});