define([
  'app',
  'tpl!../templates/form.ejs',
  'Common/views/modal',
  'Common/views/form'], function(App, formTemplate, Modal, Form){

  return App.module('Ticket.Create', function(Create, App, Backbone, Marionette, $, _){

    Create.ItemView = Form.ItemView.extend({
      template: formTemplate
    });

    Create.ModalView = Modal.LayoutView.extend({
      submitModal: function(){
        this.ModalBody.currentView.submitForm();
        this.trigger('modal:submit');
      }
    });

  });

});