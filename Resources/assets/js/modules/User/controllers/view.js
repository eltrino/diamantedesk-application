define(['app'], function(App){

  return App.module('User.View', function(View, App, Backbone, Marionette, $, _){

    View.Controller = function(options){

      require([
        'User/models/user',
        'User/views/view'], function(){

        var request = App.request('user:model:current');

        request.done(function(userModel){
          var userView = new View.ItemView({
            model: userModel
          });

          userView.on('user:edit', function(){
            App.trigger('user:edit', options);
          });

          options.parentRegion.show(userView);
        });

      });

    };

  });

});