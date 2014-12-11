define([
  'app',
  'config'], function(App, Config){

  return App.module("User", function(User, App, Backbone, Marionette, $, _){

    var currentUser;

    User.UserModel = Backbone.Model.extend({
      urlRoot : Config.apiUrl + '/user/filter'
    });

    var API = {
      getUserModel: function(username, is_current) {
        var user = new User.UserModel(),
            defer = $.Deferred();
        if(is_current && currentUser){
          defer.resolve(currentUser);
        } else {
          user.fetch({
            data : {username: username},
            success: function(data){
              if(is_current){
                currentUser = _.clone(user);
              }
              defer.resolve(data);
            }
          });
        }
        return defer.promise();
      }
    };

    App.reqres.setHandler("user:model:current", function(username){
      return API.getUserModel(App.session.get('username'), true);
    });

    App.reqres.setHandler("user:model", function(username){
      return API.getUserModel(username);
    });

  });

});

