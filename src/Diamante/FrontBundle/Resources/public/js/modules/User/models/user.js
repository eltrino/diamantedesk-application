define([
  'app',
  'config'], function(App, Config){

  return App.module('User', function(User, App, Backbone, Marionette, $, _){

    var trim = $.trim,
        currentUser;

    User.UserModel = Backbone.Model.extend({
      url : Config.apiUrl + '/desk/users/current',
      validate: function(attrs, options){
        var errors = {};
        if(!trim(attrs.email)) {
          errors.email = __('diamante_front.user.model.error.required');
        }
        if(_.indexOf(options.ignore, 'password') === -1){
          if(!trim(attrs.password)) {
            errors.password = __('diamante_front.user.model.error.required');
          } else if(attrs.password.length < 6) {
            errors.password = __('diamante_front.user.model.error.password_length');
          }
        }
        if(!_.isEmpty(errors)){
          return errors;
        }
      }
    });

    var API = {
      getCurrentUserModel: function(){
        var user = new User.UserModel(),
            defer = $.Deferred();
        if(currentUser){
          defer.resolve(currentUser);
        } else {
          user.fetch({
            success : function(model){
              currentUser = user.clone();
              defer.resolve(model);
            },
            error : function(model, xhr){
              defer.reject(model, xhr);
            }
          });
        }
        return defer.promise();
      }
    };

    App.reqres.setHandler('user:model:current', function(){
      return API.getCurrentUserModel();
    });

    App.on('session:logout:success', function(){
      currentUser = null;
    });

  });

});

