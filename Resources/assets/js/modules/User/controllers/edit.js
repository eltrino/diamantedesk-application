define(['app', 'helpers/wsse'], function(App, Wsse){

  return App.module('User.Edit', function(Edit, App, Backbone, Marionette, $, _){

    Edit.Controller = function(options){

      require([
        'User/models/user',
        'User/views/edit'], function(){

        var request = App.request('user:model:current');

        request.done(function(userModel){
          var userView = new Edit.ItemView({
            model: userModel
          });

          userView.on('form:submit', function(data){
            userView.showLoader();
            var _options = {};
            if(data.password){
              data.password = Wsse.encodePassword(data.password);
            } else {
              delete data.password;
              _options.ignore = ['password'];
            }
            this.model.save(data, _.extend(_options,{
              patch: true,
              success : function(){
                if(data.password){
                  App.session.set('password',data.password);
                }
                var opt = _.extend({message: 'User is updated'}, options);
                App.trigger('user:view', opt);
              },
              error : function(model, xhr){
                App.alert({
                  title: "Edit User Error",
                  xhr : xhr
                });
              }
            }));
          });

          options.parentRegion.show(userView);
        });

      });

    };

  });

});