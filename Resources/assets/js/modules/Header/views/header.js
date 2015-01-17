define([
  'app',
  'config',
  'tpl!../templates/header.ejs'], function(App, Config, headerTemplate){

  return App.module('Header', function(Header, App, Backbone, Marionette, $, _){

    Header.startWithParent = false;

    Header.View = Marionette.ItemView.extend({
      template: headerTemplate,
      className: 'container',

      initialize: function(options){
        this.options = options;
      },

      templateHelpers: function(){
        return {
          baseUrl: this.options.baseUrl,
          basePath: this.options.basePath
        };
      },

      ui : {
        'createTicketButton' : '.js-create-ticket',
        'searchForm' : '.js-search-form',
        'searchInput' : '.js-search-input'
      },

      events : {
        'click @ui.createTicketButton' : 'createTicketHandler',
        'submit @ui.searchForm' : 'searchTicketHandler'
      },

      createTicketHandler : function(e){
        e.preventDefault();
        App.trigger('ticket:create');
      },

      searchTicketHandler : function(e){
        e.preventDefault();
        App.trigger('ticket:search', this.ui.searchInput.val());
      }

    });

  });


});
