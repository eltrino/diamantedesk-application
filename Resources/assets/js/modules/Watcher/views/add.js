define([
  'app',
  'tpl!../templates/form.ejs',
  'Common/views/modal',
  'Common/views/form'], function(App, formTemplate, Modal, Form){

  return App.module('Ticket.View.Watcher.Add', function(Add, App, Backbone, Marionette, $, _){

    Add.ItemView = Form.LayoutView.extend({
      template: formTemplate,
      className: 'ticket-watcher-add-fowm'
    });

    Add.ModalView = Modal.LayoutView.extend({
      submitModal: function(){
        this.modalBody.currentView.submitForm();
        this.trigger('modal:submit');
      }
    });

  });

});