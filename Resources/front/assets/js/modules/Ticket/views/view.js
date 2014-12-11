define([
  'app',
  'tpl!../templates/view.ejs',
  'tpl!../templates/missing-view.ejs'], function(App, TicketViewTemplate, missingTicketViewTemplate){

  return App.module('Ticket.View', function(View, App, Backbone, Marionette, $, _){

    View.MissingView = Marionette.ItemView.extend({
      template: missingTicketViewTemplate
    });

    View.ItemView = Marionette.ItemView.extend({
      className: 'ticket-view',
      template: TicketViewTemplate,

      templateHelpers: function(){
        return {
          created : new Date(this.model.get('created_at')).toLocaleDateString()
        };
      },

      events : {
        "click .js-ticket-list" : "listTickets",
        "click .js-edit-ticket" : "editTicket"
      },

      listTickets : function(e){
        e.preventDefault();
        App.trigger('ticket:list');
      },

      editTicket : function(e){
        e.preventDefault();
        App.trigger('ticket:edit', this.model.get('id'), this.model);
      }
    });

  });

});