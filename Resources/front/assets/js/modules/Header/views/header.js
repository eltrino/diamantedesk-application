define([
  'tpl!modules/Header/templates/header.ejs'], function(headerTemplate){

  App.module('Header', function(Header, App, Backbone, Marionette, $, _){

    Header.View = Marionette.ItemView.extend({
      template: headerTemplate,
      className: 'container',

      events : {
        "click .create-ticket .btn" : "createTicketHandler"
      },

      createTicketHandler : function(e){
        e.preventDefault();
        App.trigger('task:create');
      }
    });

  });


});
