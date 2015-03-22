define([
  'app',
  'config'], function(App, Config){

  return App.module('User', function(User, App, Backbone, Marionette, $, _){

    var currentUser,
        userCache = [],
        userCacheRequest = [];

    User.UserModel = Backbone.Model.extend({
      url : Config.apiUrl + '/desk/users/current',
      validate: function(attrs, options){
        var errors = {};
        if(!attrs.email) {
          errors.email = "Can't be blank";
        }
        if(_.indexOf(options.ignore, 'password') === -1){
          if(!attrs.password) {
            errors.password = "Can't be blank";
          } else if(attrs.password.length < 6) {
            errors.password = 'Must be at least six (6) symbols';
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
            success : function(data){
              currentUser = user.clone();
              defer.resolve(data);
            },
            error : function(data){
              defer.reject(data);
            }
          });
        }
        return defer.promise();
      },
      getUserModelByName: function(username) {
        var user = new User.UserModel(),
            defer = $.Deferred();
        user.urlRoot = Config.apiUrl + '/users/filter';
        user.fetch({
          data : { username: username },
          success : function(data){
            defer.resolve(data);
          },
          error : function(data){
            defer.reject(data);
          },
          complete : function(){
            user.urlRoot = Config.apiUrl + '/users/';
          }
        });
        return defer.promise();
      },
      getUserModelById : function(id, force){
        var user = new User.UserModel({id:id}),
            defer = $.Deferred();
        if(userCache[id]) {
          defer.resolve(userCache[id]);
        }
        if(!userCacheRequest[id]){
          userCacheRequest[id] = user;
          user.fetch({
            success: function(model){
              userCache[id] = model;
              defer.resolve(model);
            },
            error: function(){
              defer.reject();
            }
          });
        } else {
          userCacheRequest[id].on('change', function(model){ defer.resolve(model); });
        }
        return defer.promise();
      }
    };

    App.reqres.setHandler('user:model:current', function(){
      return API.getCurrentUserModel();
    });

    App.reqres.setHandler('user:model:username', function(username){
      return API.getUserModelByName(username);
    });

    App.reqres.setHandler('user:model', function(id){
      return API.getUserModelById(id);
    });

  });

});

