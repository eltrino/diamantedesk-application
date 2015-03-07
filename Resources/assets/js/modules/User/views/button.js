define([
  'app',
  'tpl!../templates/user.ejs'], function(App, userTemplate){

  return App.module('User', function(User, App, Backbone, Marionette, $, _){

    User.LayoutView = Marionette.LayoutView.extend({
      template : userTemplate,
      className : 'dropdown',

      regions : {
        dropdownRegion : '#dropdown-profile'
      },

      events : {
        'click': 'viewClicked'
      },

      viewClicked : function(){
        this.trigger('user:view');
      }
    });

  });

});