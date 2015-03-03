define([
  'app',
  'tpl!../templates/user.ejs'], function(App, userTemplate){

  return App.module('User', function(User, App, Backbone, Marionette, $, _){

    User.ItemView = Marionette.ItemView.extend({
      template: userTemplate,

      templateHelpers: function(){

      },

      events: {
        'click': 'viewClicked'
      },

      viewClicked: function(){
        this.trigger('user:view', this.model);
      }
    });

  });

});