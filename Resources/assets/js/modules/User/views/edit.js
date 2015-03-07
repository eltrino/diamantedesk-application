define([
  'app',
  'tpl!../templates/form.ejs',
  'Common/views/form'], function(App, formTemplate, Form){

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
      }

    });

  });

});