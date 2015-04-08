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

      modelEvents : {
        'change' : 'updateFullName'
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
          fullName : fullname.length ? fullname.join(' ') : email
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