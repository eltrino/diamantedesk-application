define([
  'app',
  'config',
  'tpl!../templates/footer.ejs'], function(App, Config, footerTemplate){

  return App.module('Footer', function(Footer, App, Backbone, Marionette, $, _){

    Footer.startWithParent = false;

    Footer.View = Marionette.ItemView.extend({
      template: footerTemplate,
      className: 'container',

      initialize: function(options){
        this.options = options;
      },

      templateHelpers: function(){
        return {
          baseUrl: this.options.baseUrl,
          basePath: this.options.basePath
        };
      }

    });

  });


});
