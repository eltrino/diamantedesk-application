define([
  'tpl!modules/Task/templates/form.ejs',
  'modules/Common/views/modal'], function(createTemplate){

  App.module('Task.Edit', function(Edit, App, Backbone, Marionette, $, _){

    Edit.ItemView = Marionette.ItemView.extend({
      template: createTemplate
    });

    Edit.ModalView = App.Common.Modal.LayoutView.extend();
    
  });

});