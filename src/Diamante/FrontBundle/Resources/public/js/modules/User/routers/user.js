define(['app'], function(App){

  return App.module('User', function(User, App, Backbone, Marionette, $, _){

    User.startWithParent = false;

    User.Router = Marionette.AppRouter.extend({
      appRoutes: {
        'user/edit' : 'editUser'
      }
    });

    var API = {
      editUser: function(){
        require(['User/controllers/edit'], function(Edit){
          Edit.Controller();
        });
      }
    };

    App.on('user:edit', function(){
      App.debug('info', 'Event "user:edit" fired');
      App.navigate('user/edit');
      API.editUser();
    });

    User.on('start',function(){
      new User.Router({
        controller: API
      });
    });

  });

});