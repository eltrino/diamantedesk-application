define([
  'tpl!modules/Task/templates/form.ejs',
  'modules/Common/views/modal'], function(createTemplate){

  App.module('Task.Edit', function(Edit, App, Backbone, Marionette, $, _){

    Edit.ItemView = Marionette.ItemView.extend({
      template: createTemplate
    });

    Edit.ModalView = App.Common.Modal.LayoutView.extend({
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