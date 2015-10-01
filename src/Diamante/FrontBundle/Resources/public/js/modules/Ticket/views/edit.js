define([
  'app',
  'tpl!../templates/form.ejs',
  'tpl!../templates/empty-view.ejs',
  'Common/views/modal',
  'Common/views/form'], function(App, formTemplate, missingTicketViewTemplate, Modal, Form){

  return App.module('Ticket.Edit', function(Edit, App, Backbone, Marionette, $, _){

    Edit.MissingView = Marionette.ItemView.extend({
      template: missingTicketViewTemplate
    });

    Edit.ItemView = Form.ItemView.extend({
      template: formTemplate,
      className: 'ticket-edit-form'
    });

    Edit.ModalView = Modal.LayoutView.extend({
      submitModal: function(){
        this.modalBody.currentView.submitForm();
        this.trigger('modal:submit');
      }
    });
    
  });

});