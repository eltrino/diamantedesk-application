define([
  'app',
  'tpl!../templates/form.ejs',
  'tpl!../templates/missing-view.ejs',
  'modules/Common/views/modal'], function(App, createTemplate, missingTicketViewTemplate, Modal){

  return App.module('Ticket.Edit', function(Edit, App, Backbone, Marionette, $, _){

    Edit.MissingView = Marionette.ItemView.extend({
      template: missingTicketViewTemplate
    });

    Edit.ItemView = Marionette.ItemView.extend({
      template: createTemplate
    });

    Edit.ModalView = Modal.LayoutView.extend({
      submitModal: function(){
        var arr = this.$('form').serializeArray(), i = arr.length, data = {};
        for(;i--;) {
          data[arr[i].name] = arr[i].value;
        }
        this.trigger('modal:submit', data);
      }
    });
    
  });

});