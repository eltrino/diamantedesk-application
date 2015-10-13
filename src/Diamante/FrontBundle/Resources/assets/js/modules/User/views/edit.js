define([
  'app',
  'tpl!../templates/form.ejs',
  'Common/views/form',
  'Common/views/modal',
  'cryptojs.md5',
  'pwstrength'], function(App, formTemplate, Form, Modal, MD5){

  return App.module('User.Edit', function(Edit, App, Backbone, Marionette, $, _){

    Edit.ItemView = Form.ItemView.extend({
      template : formTemplate,
      className : 'profile-edit',

      events : {
        'click @ui.submitButton' : 'submitForm',
        'click' : 'click'
      },

      click : function(e){
        e.stopPropagation();
      },

      onShow : function(){
        this.$(':password').pwstrength();
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
          avatar_url : 'http://www.gravatar.com/avatar/' + MD5(this.model.get('email'))
        };
      }

    });

    Edit.ModalView = Modal.LayoutView.extend({
      submitModal: function(){
        this.modalBody.currentView.submitForm();
        this.trigger('modal:submit');
      }
    });

  });

});