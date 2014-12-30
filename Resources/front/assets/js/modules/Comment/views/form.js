define([
  'app',
  'Common/views/form',
  'tpl!../templates/form.ejs'], function(App, CommonForm, formTemplate){

  return App.module('Ticket.View.Comment.Form', function(Form, App, Backbone, Marionette, $, _){

    Form.ItemView = CommonForm.ItemView.extend({
      template : formTemplate
    });

  });

});