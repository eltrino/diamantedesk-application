define(['app'], function(App){

  return App.module('User', function(User, App, Backbone, Marionette, $, _){

    User.Controller = function(options){

      require([
        'User/models/user',
        'User/views/button'], function(){

        var request = App.request('user:model:current');

        request.done(function(userModel){
          var userButton = new User.LayoutView({
            model: userModel
          });

          userButton.on('user:view', function(){
            App.trigger('user:view', {
              parentRegion: userButton.dropdownRegion
            });
          });

          options.parentRegion.show(userButton);
        });

      });

    };

  });

});