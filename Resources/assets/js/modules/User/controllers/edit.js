define(['app', 'helpers/wsse'], function(App, Wsse){

  return App.module('User.Edit', function(Edit, App, Backbone, Marionette, $, _){

    Edit.Controller = function(options){

      require([
        'User/models/user',
        'User/views/edit'], function(){

        var request = App.request('user:model:current');

        request.done(function(userModel){
          var userEditView = new Edit.ItemView({
            model: userModel
          });

          userEditView.on('form:submit', function(data){
            var ignore = [];
            if(data.password){
              data.password = Wsse.encodePassword(data.password);
            } else {
              delete data.password;
              ignore = ['password'];
            }
            this.model.save(data,{
              ignore: ignore,
              patch: true,
              success : function(){
                if(data.password){
                  App.session.set('password', data.password);
                }
                options.message = 'User is updated';
                console.log(options.parentRegion);
                App.trigger('user:view', options);
              },
              error : function(model, xhr){
                App.alert({
                  title: "Edit User Error",
                  xhr : xhr
                });
              }
            });
          });

          options.parentRegion.show(userEditView);
        });

      });

    };

  });

});