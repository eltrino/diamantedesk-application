define([
  'app',
  'config'], function(App, Config){

  return App.module("User", function(User, App, Backbone, Marionette, $, _){

    var currentUser,
        userCache = [];

    User.UserModel = Backbone.Model.extend({
      urlRoot : Config.apiUrl + '/users/'
    });

    var API = {
      getUserModelByName: function(username, is_current) {
        var user = new User.UserModel(),
            defer = $.Deferred();
        if(is_current && currentUser){
          defer.resolve(currentUser);
        } else {
          user.urlRoot = Config.apiUrl + '/user/filter';
          user.fetch({
            data : { username: username },
            success : function(data){
              if(is_current){
                currentUser = _.clone(user);
              }
              defer.resolve(data);
            },
            complete : function(){
              user.urlRoot = Config.apiUrl + '/users/';
            }
          });
        }
        return defer.promise();
      },
      getUserModelById : function(id, force){
        var user = new User.UserModel({id:id}),
            defer = $.Deferred();
        if(userCache[id] && !force){
          defer.resolve(userCache[id]);
        } else {
          user.fetch({
            success: function(data){
              userCache[id] = data;
              defer.resolve(data);
            },
            error: function(){
              defer.reject();
            }
          });
        }
        return defer.promise();
      }
    };

    App.reqres.setHandler("user:model:current", function(){
      return API.getUserModelByName(App.session.get('username'), true);
    });

    App.reqres.setHandler("user:model:username", function(username){
      return API.getUserModelByName(username);
    });

    App.reqres.setHandler("user:model", function(id){
      return API.getUserModelById(id);
    });

  });

});

