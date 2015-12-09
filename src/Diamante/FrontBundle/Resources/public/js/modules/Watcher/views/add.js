define([
  'app',
  'tpl!../templates/form.ejs',
  'Common/views/modal',
  'Common/views/form'], function(App, formTemplate, Modal, Form){

  return App.module('Ticket.View.Watcher.Add', function(Add, App, Backbone, Marionette, $, _){

    Add.ItemView = Form.LayoutView.extend({
      template: formTemplate,
      className: 'ticket-watcher-add-form'
    });

    Add.ModalView = Modal.LayoutView.extend({
      beforeShowModal : function(){
        window.history.pushState("","",window.location.href + '/modal');
        Modal.LayoutView.prototype.beforeShowModal.call(this);
      },
      beforeHideModal : function(){
        window.history.pushState("","",window.location.href.replace('/modal',''));
        Modal.LayoutView.prototype.beforeHideModal.call(this);
      },
      submitModal: function(){
        this.modalBody.currentView.submitForm();
        this.trigger('modal:submit');
      }
    });

  });

});