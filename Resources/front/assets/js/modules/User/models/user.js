define([
  'app',
  'config'], function(App, Config){

  return App.module("User", function(User, App, Backbone, Marionette, $, _){

    var currentUser;

    User.UserModel = Backbone.Model.extend({
      urlRoot : Config.apiUrl + '/user/'
    });

    var API = {
      getUserModelByName: function(username, is_current) {
        var user = new User.UserModel(),
            defer = $.Deferred();
        if(is_current && currentUser){
          defer.resolve(currentUser);
        } else {
          user.urlRoot += 'filter';
          user.fetch({
            data : {username: username},
            success : function(data){
              if(is_current){
                currentUser = _.clone(user);
              }
              defer.resolve(data);
            },
            complete : function(){
              user.urlRoot = user.urlRoot.replace('filter','');
            }
          });
        }
        return defer.promise();
      },
      getUserModelById : function(id){
        var user = new User.UserModel({id:id}),
            defer = $.Deferred();
        user.fetch({
          success: function(data){
            defer.resolve(data);
          },
          error: function(){
            defer.reject();
          }
        });
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

