define([
  'app',
  'tpl!../templates/user.ejs',
  'cryptojs.md5'], function(App, userTemplate, MD5){

  return App.module('User', function(User, App, Backbone, Marionette, $, _){

    User.LayoutView = Marionette.LayoutView.extend({
      template : userTemplate,
      className : 'profile-wrapper',

      ui : {
        logoutButton : '.js-logout',
        editButton : '.js-edit-user',
        message: '.alert'
      },

      events : {
        'click @ui.logoutButton' : 'logout',
        'click @ui.editButton' : 'editUser',
        'click' : 'click'
      },

      modelEvents : {
        'change' : 'updateFullName'
      },

      initialize : function(options){
        this.message = options.message;
      },

      logout : function(){
        App.trigger('session:logout');
      },

      editUser : function(e){
        // Remove in order to implement routing via #edit-user hash tag;
        e.preventDefault();
        e.stopPropagation();
        this.trigger('user:edit');
      },

      click : function(e){
        e.stopPropagation();
      },

      templateHelpers : function(){
        var email = this.model.get('email'),
            fullname = [];
        if(this.model.get('first_name')) {
          fullname.push(this.model.get('first_name'));
        }
        if(this.model.get('last_name')) {
          fullname.push(this.model.get('last_name'));
        }
        return {
          fullName : fullname.length ? fullname.join(' ') : email,
          avatar_url : 'http://www.gravatar.com/avatar/' + MD5(this.model.get('email')),
          message : this.message
        };
      },

      updateFullName: function(){
        this.$('.user-name').text(this.templateHelpers().fullName);
      },

      viewClicked : function(){
        this.dropdownRegion.showLoader();
        this.trigger('user:view');
      }
    });

  });

});
