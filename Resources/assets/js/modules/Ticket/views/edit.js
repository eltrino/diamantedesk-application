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
      className: 'ticket-edit-form',
      onShow : function(){
        var textarea =  this.$('textarea');
        textarea.keyup(function(){
          var height = this.clientHeight;
          if(this.clientHeight < this.scrollHeight){
            while(this.clientHeight < this.scrollHeight) {
              $(this).height(++height);
            }
          }
        });
        App.dialogRegion.$el.on('shown.bs.modal', function(){ textarea.keyup(); });
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