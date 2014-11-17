define([
  'tpl!modules/Task/templates/form.ejs',
  'modules/Common/views/modal'], function(createTemplate){

  App.module('Task.Create', function(Create, App, Backbone, Marionette, $, _){

    Create.ItemView = Marionette.ItemView.extend({
      template: createTemplate
    });

    Create.ModalView = App.Common.Modal.LayoutView.extend();

  });

});