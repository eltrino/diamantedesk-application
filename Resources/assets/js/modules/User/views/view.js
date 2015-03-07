define([
  'app',
  'tpl!../templates/view.ejs',
  'cryptojs.md5'], function(App, userViewTemplate, MD5){

  return App.module('User.View', function(View, App, Backbone, Marionette, $, _){

    View.ItemView = Marionette.ItemView.extend({
      template : userViewTemplate,
      className : 'profile-view',

      ui : {
        logoutButton : '.js-logout',
        editButton : '.js-edit-user'
      },

      events : {
        'click @ui.logoutButton' : 'logout',
        'click @ui.editButton' : 'editUser',
        'click' : 'click'
      },

      templateHelpers : function(){
        return {
          avatar_url : 'http://www.gravatar.com/avatar/' + MD5(this.model.get('email'))
        };
      },

      logout : function(){
        App.trigger('session:logout');
      },

      editUser : function(e){
        e.stopPropagation();
        this.trigger('user:edit');
      },

      click : function(e){
        e.stopPropagation();
      }

    });

  });

});