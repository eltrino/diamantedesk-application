define([
  'tpl!modules/Task/templates/form.ejs',
  'modules/Common/views/modal'], function(createTemplate){

  App.module('Task.Create', function(Create, App, Backbone, Marionette, $, _){

    Create.ItemView = Marionette.ItemView.extend({
      template: createTemplate
    });

    Create.ModalView = App.Common.Modal.LayoutView.extend({
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